#!/usr/bin/env python3
"""
SINTA Kemdiktisaintek Scraper & WordPress Sync Client
Fetches active lecturer SINTA IDs from WordPress REST API, scrapes SINTA
(metrics, Scopus/Garuda/Google Scholar publications, research, community service,
and books), and pushes the merged payload back to WordPress.
"""

import argparse
import json
import os
import re
import sys
import time
from typing import Any, Dict, List, Optional

import requests
from bs4 import BeautifulSoup

SINTA_BASE = "https://sinta.kemdiktisaintek.go.id/authors/profile"
USER_AGENT = (
    "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 "
    "(KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36"
)


def parse_int_id(val: str) -> int:
    """Parse Indonesian formatted numbers like '1.083' -> 1083."""
    cleaned = re.sub(r"[^\d]", "", val or "")
    return int(cleaned) if cleaned else 0


def normalize_title(title: str) -> str:
    return re.sub(r"[^a-z0-9]+", "", (title or "").lower())


def fetch_html(session: requests.Session, url: str, retries: int = 3) -> Optional[str]:
    for attempt in range(retries):
        try:
            resp = session.get(url, timeout=20)
            if resp.status_code == 200:
                return resp.content.decode("utf-8", errors="replace")
            if resp.status_code in (429, 500, 502, 503, 504):
                time.sleep(2 * (attempt + 1))
                continue
            return None
        except requests.RequestException:
            time.sleep(2 * (attempt + 1))
    return None


def make_soup(html: str) -> BeautifulSoup:
    try:
        return BeautifulSoup(html, "lxml")
    except Exception:
        return BeautifulSoup(html, "html.parser")


def parse_profile_metrics(html: str) -> Dict[str, int]:
    soup = make_soup(html)
    metrics: Dict[str, int] = {
        "sinta_score_overall": 0,
        "sinta_score_3yr": 0,
        "scopus_articles": 0,
        "scopus_citations": 0,
        "scopus_h_index": 0,
        "gs_articles": 0,
        "gs_citations": 0,
        "gs_h_index": 0,
    }

    for num_el in soup.select(".pr-num"):
        txt_el = num_el.find_next_sibling(class_="pr-txt")
        if not txt_el:
            continue
        label = txt_el.get_text(" ", strip=True).lower()
        val = parse_int_id(num_el.get_text(" ", strip=True))
        if "sinta score overall" in label:
            metrics["sinta_score_overall"] = val
        elif "sinta score 3yr" in label:
            metrics["sinta_score_3yr"] = val

    stat_table = soup.select_one("table.stat-table")
    if stat_table:
        for row in stat_table.select("tr"):
            cols = [c.get_text(" ", strip=True) for c in row.select("th, td")]
            if len(cols) < 3:
                continue
            row_label = cols[0].lower()
            if row_label == "article":
                metrics["scopus_articles"] = parse_int_id(cols[1])
                metrics["gs_articles"] = parse_int_id(cols[2])
            elif row_label == "citation":
                metrics["scopus_citations"] = parse_int_id(cols[1])
                metrics["gs_citations"] = parse_int_id(cols[2])
            elif row_label == "h-index":
                metrics["scopus_h_index"] = parse_int_id(cols[1])
                metrics["gs_h_index"] = parse_int_id(cols[2])

    return metrics


def extract_year(text: str) -> int:
    m = re.search(r"\b(19\d{2}|20\d{2})\b", text or "")
    return int(m.group(1)) if m else 0


def clean_author_tokens(author_name: str) -> List[str]:
    base = (author_name or "").split(",")[0]
    base = re.sub(r"\b(prof|dr|dra|drs|ir|h|hj)\b\.?", " ", base, flags=re.I)
    return [t.lower() for t in re.findall(r"[a-zA-Z]{2,}", base)]


def is_first_author_by_name(authors_str: str, author_name: str) -> bool:
    if not authors_str or not author_name:
        return False
    tokens = clean_author_tokens(author_name)
    if not tokens:
        return False
    if ";" in authors_str:
        first_author = authors_str.split(";", 1)[0].strip().lower()
    elif "," in authors_str:
        first_author = authors_str.split(",", 1)[0].strip().lower()
    else:
        return True  # Single author
    last_name = tokens[-1]
    if re.search(rf"\b{re.escape(last_name)}\b", first_author):
        return True
    if len(tokens) > 1 and len(tokens[0]) >= 4 and re.search(rf"\b{re.escape(tokens[0])}\b", first_author):
        return True
    return False


def parse_publications(html: str, source_type: str, author_name: str = "") -> List[Dict[str, Any]]:
    soup = make_soup(html)
    items: List[Dict[str, Any]] = []

    for card in soup.select(".ar-list-item"):
        title_a = card.select_one(".ar-title a")
        if not title_a:
            continue
        judul = title_a.get_text(" ", strip=True)
        if not judul:
            continue
        url = (title_a.get("href") or "").strip()
        if url == "#!":
            url = ""

        pub_el = card.select_one(".ar-pub")
        media = pub_el.get_text(" ", strip=True) if pub_el else ""

        year_el = card.select_one(".ar-year")
        tahun = extract_year(year_el.get_text(" ", strip=True) if year_el else "")

        quart_el = card.select_one(".ar-quartile")
        quart_txt = quart_el.get_text(" ", strip=True) if quart_el else ""

        akreditasi = ""
        author_order = None
        penulis = ""

        meta_links = [a for a in card.select(".ar-meta a")]
        for ma in meta_links:
            mt = ma.get_text(" ", strip=True)
            lower = mt.lower()
            if lower.startswith("author order"):
                m_ord = re.search(r":\s*(\d+)\b", mt)
                if m_ord:
                    author_order = int(m_ord.group(1))
            elif lower.startswith("creator :"):
                penulis = mt.split(":", 1)[1].strip()
            elif lower.startswith("authors :"):
                penulis = mt.split(":", 1)[1].strip()
            elif (
                source_type == "GARUDA"
                and not penulis
                and "ar-pub" not in (ma.get("class") or [])
                and "ar-year" not in (ma.get("class") or [])
                and "ar-quartile" not in (ma.get("class") or [])
                and not lower.startswith("doi:")
                and not lower.startswith("accred")
                and (";" in mt or "," in mt)
            ):
                penulis = mt

        if author_order is not None and author_order > 0:
            peran = "Penulis Utama" if author_order == 1 else "Anggota"
        elif is_first_author_by_name(penulis, author_name):
            peran = "Penulis Utama"
        else:
            peran = "Anggota"

        if source_type == "Scopus":
            m_q = re.search(r"\b(Q[1-4]|no-Q)\b", quart_txt, re.I)
            akreditasi = m_q.group(1) if m_q else "Scopus"
        elif source_type == "GARUDA":
            m_s = re.search(r"\b(SINTA\s*[1-6])\b", quart_txt, re.I)
            akreditasi = m_s.group(1).upper() if m_s else "GARUDA"
        else:
            akreditasi = "Scholar"

        items.append(
            {
                "judul": judul,
                "tahun": tahun,
                "media": media,
                "akreditasi": akreditasi,
                "peran": peran,
                "url": url,
                "sumber": source_type,
                "penulis": penulis,
            }
        )

    return items


def parse_researches_or_services(
    html: str, is_service: bool = False, author_name: str = ""
) -> List[Dict[str, Any]]:
    soup = make_soup(html)
    items: List[Dict[str, Any]] = []

    for card in soup.select(".ar-list-item"):
        title_a = card.select_one(".ar-title a")
        if not title_a:
            continue
        judul = title_a.get_text(" ", strip=True)
        if not judul:
            continue

        pub_el = card.select_one(".ar-pub")
        sumber_dana = pub_el.get_text(" ", strip=True) if pub_el else (
            "PNBP UNM" if is_service else "KEMENDIKBUDRISTEK"
        )

        year_el = card.select_one(".ar-year")
        tahun = extract_year(year_el.get_text(" ", strip=True) if year_el else "")

        leader = ""
        for a in card.select(".ar-meta a"):
            txt = a.get_text(" ", strip=True)
            if txt.lower().startswith("leader :"):
                leader = txt.split(":", 1)[1].strip()
                break

        is_leader = (
            bool(author_name)
            and bool(leader)
            and author_name.lower().split(",")[0].strip() in leader.lower()
        )
        if is_service:
            peran = "Ketua Pelaksana" if is_leader else (f"Ketua: {leader}" if leader else "Anggota")
            items.append(
                {
                    "judul": judul,
                    "tahun": tahun,
                    "peran": peran,
                    "sumber_dana": sumber_dana,
                    "lokasi": "",
                }
            )
        else:
            peran = "Ketua Peneliti" if is_leader else (f"Ketua: {leader}" if leader else "Anggota")
            items.append(
                {
                    "judul": judul,
                    "tahun": tahun,
                    "peran": peran,
                    "sumber_dana": sumber_dana,
                    "lama_tahun": 1,
                }
            )

    return items


def parse_books(html: str) -> List[Dict[str, Any]]:
    soup = make_soup(html)
    items: List[Dict[str, Any]] = []

    for card in soup.select(".ar-list-item"):
        title_a = card.select_one(".ar-title a")
        if not title_a:
            continue
        judul = title_a.get_text(" ", strip=True)
        if not judul:
            continue

        pub_el = card.select_one(".ar-pub")
        penerbit = pub_el.get_text(" ", strip=True) if pub_el else ""

        year_el = card.select_one(".ar-year")
        tahun = extract_year(year_el.get_text(" ", strip=True) if year_el else "")

        quart_el = card.select_one(".ar-quartile")
        isbn_txt = quart_el.get_text(" ", strip=True) if quart_el else ""
        isbn = re.sub(r"^ISBN\s*:\s*", "", isbn_txt, flags=re.I).strip()

        items.append(
            {
                "judul": judul,
                "tahun": tahun,
                "penerbit": penerbit,
                "isbn": isbn,
            }
        )

    return items


def scrape_author(
    session: requests.Session, sinta_id: str, author_name: str = ""
) -> Optional[Dict[str, Any]]:
    base = f"{SINTA_BASE}/{sinta_id}"
    profile_html = fetch_html(session, base)
    if not profile_html:
        return None

    metrics = parse_profile_metrics(profile_html)
    time.sleep(0.8)

    # Publications: Scopus > GARUDA > Google Scholar (deduplicated)
    seen_titles = set()
    publikasi: List[Dict[str, Any]] = []

    for view, label in [
        ("scopus", "Scopus"),
        ("garuda", "GARUDA"),
        ("googlescholar", "Google Scholar"),
    ]:
        vh = fetch_html(session, f"{base}/?view={view}")
        if vh:
            for item in parse_publications(vh, label, author_name):
                norm = normalize_title(item["judul"])
                if norm and norm not in seen_titles:
                    seen_titles.add(norm)
                    publikasi.append(item)
        time.sleep(0.8)

    publikasi.sort(key=lambda x: x.get("tahun", 0), reverse=True)

    res_html = fetch_html(session, f"{base}/?view=researches")
    penelitian = parse_researches_or_services(res_html, False, author_name) if res_html else []
    time.sleep(0.8)

    srv_html = fetch_html(session, f"{base}/?view=services")
    pengabdian = parse_researches_or_services(srv_html, True, author_name) if srv_html else []
    time.sleep(0.8)

    book_html = fetch_html(session, f"{base}/?view=books")
    buku = parse_books(book_html) if book_html else []

    return {
        "sinta_id": sinta_id,
        "sinta_metrics": metrics,
        "publikasi": publikasi,
        "penelitian": penelitian,
        "pengabdian": pengabdian,
        "buku": buku,
    }


def main() -> int:
    parser = argparse.ArgumentParser(description="Sync SINTA data to WordPress")
    parser.add_argument("--sinta-id", help="Only scrape a specific SINTA ID")
    parser.add_argument("--dry-run", action="store_true", help="Print JSON instead of pushing to WP")
    args = parser.parse_args()

    session = requests.Session()
    session.headers.update({"User-Agent": USER_AGENT})

    if args.sinta_id and args.dry_run:
        res = scrape_author(session, args.sinta_id)
        if not res:
            print(f"Failed to scrape SINTA ID {args.sinta_id}", file=sys.stderr)
            return 1
        print(json.dumps(res, indent=2, ensure_ascii=False))
        return 0

    wp_url = (os.environ.get("WP_SITE_URL") or "").rstrip("/")
    token = os.environ.get("BK_SINTA_SYNC_TOKEN") or ""
    if not wp_url or not token:
        print("Error: WP_SITE_URL and BK_SINTA_SYNC_TOKEN must be set.", file=sys.stderr)
        return 1

    wp_headers = {
        "Authorization": f"Bearer {token}",
        "Content-Type": "application/json",
        "User-Agent": USER_AGENT,
    }

    roster_resp = requests.get(
        f"{wp_url}/wp-json/bk-fip/v1/dosen-sinta-ids",
        headers=wp_headers,
        timeout=30,
    )
    if roster_resp.status_code != 200:
        print(
            f"Failed to fetch lecturer SINTA IDs: HTTP {roster_resp.status_code} {roster_resp.text}",
            file=sys.stderr,
        )
        return 1

    dosen_list = roster_resp.json().get("dosen", [])
    if args.sinta_id:
        dosen_list = [d for d in dosen_list if str(d.get("sinta_id")) == str(args.sinta_id)]

    print(f"Found {len(dosen_list)} lecturers with SINTA IDs.")
    updates: List[Dict[str, Any]] = []

    for idx, d in enumerate(dosen_list, start=1):
        sid = str(d.get("sinta_id") or "").strip()
        slug = d.get("slug") or ""
        nama = d.get("nama") or slug
        if not sid:
            continue

        print(f"[{idx}/{len(dosen_list)}] Scraping {nama} (SINTA: {sid})...")
        scraped = scrape_author(session, sid, nama)
        if scraped:
            scraped["slug"] = slug
            updates.append(scraped)
        else:
            print(f"  -> Warning: failed to scrape {sid}", file=sys.stderr)

        # Push in batches of 5 to keep payloads small
        if len(updates) >= 5 and not args.dry_run:
            push_batch(wp_url, wp_headers, updates)
            updates = []

        time.sleep(1.2)

    if updates:
        if args.dry_run:
            print(json.dumps(updates, indent=2, ensure_ascii=False))
        else:
            push_batch(wp_url, wp_headers, updates)

    print("SINTA sync completed.")
    return 0


def push_batch(wp_url: str, headers: Dict[str, str], updates: List[Dict[str, Any]]) -> None:
    resp = requests.post(
        f"{wp_url}/wp-json/bk-fip/v1/sync-sinta",
        headers=headers,
        json={"updates": updates},
        timeout=45,
    )
    if resp.status_code != 200:
        print(f"  -> Batch push error: HTTP {resp.status_code} {resp.text}", file=sys.stderr)
    else:
        data = resp.json()
        print(f"  -> Pushed {len(updates)} lecturers (updated={data.get('updated', 0)}).")


if __name__ == "__main__":
    sys.exit(main())
