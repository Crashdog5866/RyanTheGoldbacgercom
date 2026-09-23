# Ryan Goldbacher Website Changelog

## 2026-09-18 - Site Redesign to Match Rancid Aesthetic


## 2026-09-18 — Grouped Tour Dates and Official Links

### Current Tour Dates Section
- Reduced each artist block to a narrower three-column layout so the blocks sit side by side on desktop; they remain stacked on smaller screens.
- Moved each “Visit [artist] official website” link beneath the artist name instead of beside it.
- Fixed the right-edge clipping by using a full-width three-column grid, wrapping long artist and venue names, and adding responsive two-column and single-column breakpoints.

- Replaced the mixed grid of individual cards with one block per artist.
- Each block now starts with the artist name as the section heading.
- Each show is listed underneath as:
  - Date
  - City
  - Venue
- Added an official artist-website link to each artist heading for SEO and navigation.
- Tom Keifer shows “No upcoming tour dates currently listed.” because his official site currently has no upcoming dates.
- Applied the same grouped layout to both:
  - `/`
  - `/tours/`

### New Files

- `src/data/tourDates.ts` — structured artist/date data used by both tour pages.
- `src/components/TourDates.astro` — reusable grouped tour-date component.

### Updated Files

- `src/pages/index.astro`
- `src/pages/tours.astro`

### Verified Tour Data

- Texas Is The Reason: 9 dates from https://www.texasisthereason.com/
- Boys Like Girls: 2 dates from https://www.boyslikegirls.com/
- Tom Keifer: no upcoming dates listed on https://www.tomkeifer.com/events/

### Build

- `npx astro build` completed successfully.
- Both `/` and `/tours/` were rebuilt and verified.

### Changes Made

#### 1. Design Updates
- **Hero Section**: Rebranded to Rancid-inspired design
  - Bold condensed typography ("KEEP THE SHOW MOVING. MAKE IT SOUND UNFORGETTABLE.")
  - Dark theme with full-bleed hero background
  - Added "Pilot" to role titles in hero and footer
  - Changed stats header from "4 decades" to "20+ years" to match resume data

#### 2. Navigation
- **Fixed Resume Link**: Added `#resume` nav link (was missing)
- Added `#tour` nav link
- Reorganized nav order: Resume → Tour → Content → Contact

#### 3. Tour Dates Page (/tours/)
- **Removed unverified dates**: Corrected dates that didn't match official band websites
- **Texas Is The Reason** (texasisthereason.com):
  - Oct 20, 2026 – Union Stage, Washington, DC
  - Oct 21, 2026 – Amos' Southend, Charlotte, NC
  - Oct 22, 2026 – The Masquerade, Atlanta, GA
  - Oct 24, 2026 – Fest, Gainesville, FL
  - Nov 4, 2026 – O-nest, Tokyo, Japan
  - Nov 5, 2026 – WWWX, Tokyo, Japan
  - Nov 7, 2026 – Live & Lounge Vio, Nagoya, Japan
  - Nov 8, 2026 – Socore Factory, Osaka, Japan
  - Nov 12, 2026 – The Truth, Nashville, TN
- **Boys Like Girls** (boyslikegirls.com):
  - Sep 26, 2026 – Four Chord Music Festival, Pittsburgh, PA
  - Nov 20, 2026 – Shillong Indian International Cherry Blossom Festival, Shillong, India
- **Tom Keifer** (tomkeifer.com):
  - "Dates pending confirmation" (artist shows "No Upcoming Tour Dates")

#### 4. Contact Page (/contact/)
- **Replaced social-only contact block** with actual contact form
- Form includes: Name, Email, Subject, Message
- Social links moved to footer section

#### 5. Field Notes Section
- **Removed unverified testimonial quotes**
- Now contains descriptive text + YouTube/Instagram links

#### 6. Footer Updates
- Added "Pilot" to role listing
- Consistent with hero section

### Files Modified
- `src/pages/index.astro` – Main page with hero, services, tour dates, contact
- `src/pages/tours.astro` – Dedicated tour schedule page
- `src/pages/contact.astro` – Contact form page
- `src/pages/resume.astro` – Resume page (existing)
- `src/layouts/BaseLayout.astro` – Layout wrapper (existing)
- `src/styles/global.css` – Styling (existing)

### Backup Files Created
- `src/pages/index.astro.bak` – Original index.astro
- `src/pages/contact.astro.bak` – Original contact.astro

### Build Status
- ✓ Astro build completed successfully
- ✓ All pages generated: /, /blog/, /tours/, /resume/, /contact/
- ✓ Site running locally at http://localhost:8099/

### External Resources Verified
- Texas Is The Reason: https://www.texasisthereason.com/
- Boys Like Girls: https://www.boyslikegirls.com/
- Tom Keifer: https://www.tomkeifer.com/events/

---
*Created: 2026-09-18*

### Added Active Touring Bands

- Added Accept (Nov 19–21, 2026), Sebastian Bach (Oct–Dec 2026), and Brother Cane (Dec 4, 2026) to the tour schedule.
- Added responsive text scaling and overflow wrapping so long artist names, venues, and links stay inside each card.
- Updated `/tours/` metadata to list the expanded tour schedule.
