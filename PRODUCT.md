# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Stack

Jigsaw on PHP 8.4, a static site, as axisofstevil.com is: built from the channel's public API, deployed to S3 behind CloudFront by GitHub Actions on a push and every morning at 6:05 Eastern. The Jekyll site it replaced is kept at the tag `circa-2026`.

## Users

- **Listeners mid-episode** (primary): someone who heard the CFO say "Exhibit 3 is in your packet" and came to see it. The episode page is the shareholder packet.
- **Archive fans** (primary): people who know axisofstevil.com and want its works read aloud, browsing the run quarter by quarter and following each piece back to the original.
- Newcomers from search or a shared link, deciding whether to listen and subscribe (secondary).

## Product Purpose

The Axis of Stevil Show is a podcast made from the axisofstevil.com archive: a deadpan quarterly earnings call, hosted by the CFO, Juan Conroy, presenting the archive's works as the company's results, quarter by quarter from Q1 2005 to Q2 2007, about 100 short episodes. The site is a **full listening site**: every episode plays here, with its chapters, its exhibits and its transcript, and the site is also where people subscribe in their own podcast app.

Success: a listener finds the exhibit the call points to within seconds, keeps listening from one episode to the next, and follows the works back to axisofstevil.com.

## Positioning

The only show read from the Axis of Stevil archive itself, by the company's own (fictional) CFO; the original works are linked from every episode. The current axisofstevilshow.com (112 machine-written summaries) is replaced; every old address forwards to its work.

## Operating Context

- **The run:** a season is a quarter (Season 1 is Q1 '05, named "Q1 '05"); an episode is a part of its quarter ("Q2 '05, Part 1"). Episodes release in strict order, one a week, Mondays at 6:00 Eastern, after three on launch day.
- **An episode** has a title (its hook, "Two Birds, One Stone"), show notes in the CFO's voice opening with its quarter and part, the MP3, chapters (each with a picture: the piece's own, or a chapter plate), numbered exhibits (archive pictures with alt text), a transcript, and the axisofstevil works it uses.
- **The show's art:** a cover and ten chapter plates in the annual-report style with the axisofstevil symbol, at `archive.axisofstevil.com/earnings-calls/`.
- **The feed** (`feed.xml`, iTunes and Podcasting 2.0: chapters, transcript, the AI disclosure) is built by the site; Apple Podcasts and Spotify read it. The show is episodic (newest first) and listed as exactly "The Axis of Stevil Show".
- Data comes from the channel's public API (an `episode` block on each entry); media comes from `archive.axisofstevil.com` by full address.

## Capabilities and Constraints

- Every episode plays on its own page; listening carries on across pages (a persistent player), with chapters to jump to and the transcript to follow.
- Every page offers subscribing in the listener's own app; the feed is the source of truth for the apps.
- The site lists the run in order (quarter, then part), whatever the release dates.
- **Disclosure:** the CFO's voice is synthetic and his remarks were written with assistance from AI; the archive's works were written by people. The site says so on every episode and in the show's description (Apple Podcasts 1.11), and says the show is parody, read from an archive of satire.
- Every old `/show/{yyyy-mm-dd-slug}/` address forwards permanently to its work on axisofstevil.com.
- The repository is public: it holds the site only, never the private tooling that feeds it.
- Undecided: the listening apps to link beyond Apple Podcasts and Spotify; analytics (download counts come from the archive's logs, later).

## Brand Commitments

- **The name** "The Axis of Stevil Show", exactly.
- **A sibling of axisofstevil.com, never mistaken for it**: it resonates with the archive in style and theme (the annual report's plates, type and symbol), but nobody should wonder which site they are on. The show carries its own name everywhere the archive carries its own, and says plainly that the works come from axisofstevil.com.
- **The cover and plates** already made (`archive.axisofstevil.com/earnings-calls/cover.jpg`, `plate-{type}.jpg`).
- **The site speaks the call's language**: calls, the call summary, the current call, the next call. Only the parts that know it is a show outside the bit (the feed's directory fields, the private tooling) say show and episode.
- **Played straight:** the show is a mock-official earnings call; the copy is in the CFO's dry, fallible, corporate voice. Stevil is on the call and is never heard.

## Evidence on Hand

- Five recorded episodes (Q1 '05 Parts 1–2, Q2 '05 Parts 1–3), with their notes, chapters, exhibits and transcripts, served by the channel API once released.
- The cover and ten chapter plates.
- No listener numbers, reviews or press: none may be invented.

## Product Principles

1. **The packet is the page.** An episode page answers "where is Exhibit 3?" at a glance while the audio plays.
2. **Listening never stops.** Moving around the site keeps the episode playing.
3. **Every episode is a door to the archive.** The works it reads are a click away on axisofstevil.com.
4. **Played straight.** The joke lives in the voice and the content; the presentation is an earnest, well-made investor relations site.
5. **In order.** The run reads quarter by quarter, part by part.

## Accessibility & Inclusion

- Every exhibit carries its alt text; every episode its full transcript.
- The player works by keyboard and screen reader; nothing plays without the listener's say.
