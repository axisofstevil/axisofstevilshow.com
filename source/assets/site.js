/*
 * The call: one audio element in the dock (kept across Turbo visits), the run's playlist beside it. An episode
 * page's console plays that episode through the dock; while it plays, the clock runs, the agenda item now
 * speaking lights, and the packet turns to the exhibit being discussed. Each call remembers where you stopped.
 */
(function () {
    const dock = document.querySelector('[data-dock]');
    if (!dock) return;

    const audio = dock.querySelector('[data-audio]');
    let playlist = [];
    try { playlist = JSON.parse(dock.querySelector('[data-playlist]').textContent || '[]'); } catch (e) { playlist = []; }
    const bySlug = Object.fromEntries(playlist.map((call) => [call.slug, call]));
    let current = null;

    // The closing music: every call ends on a 12-second outro after its last words (the scripts' [music outro 0:12]).
    const OUTRO = 12;
    const clock = (seconds) => {
        const s = Math.max(0, Math.floor(seconds || 0));
        return Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0');
    };
    const remember = (slug, at) => { try { localStorage.setItem('show.at.' + slug, String(Math.floor(at))); } catch (e) {} };
    const recall = (slug) => { try { return Number(localStorage.getItem('show.at.' + slug)) || 0; } catch (e) { return 0; } };
    const chapterAt = (call, at) => {
        let index = 0;
        call.chapters.forEach((chapter, i) => { if (chapter.start <= at + 0.25) index = i; });
        return index;
    };

    function load(slug, at) {
        const call = bySlug[slug];
        if (!call) return false;
        if (!current || current.slug !== slug) {
            current = call;
            audio.src = call.audio;
            audio.currentTime = 0;
            const start = at ?? recall(slug);
            if (start > 0) audio.addEventListener('loadedmetadata', () => { audio.currentTime = start; }, { once: true });
            dock.querySelector('[data-dock-title]').textContent = call.title;
            dock.querySelector('[data-dock-season]').textContent = call.label;
            dock.querySelector('[data-dock-link]').href = call.url;
            dock.querySelector('[data-length]').textContent = clock(call.duration);
            // The dock's timeline: one segment per chapter, in proportion, each named; a click lands where it falls.
            const track = dock.querySelector('[data-track]');
            track.replaceChildren(...call.chapters.map((chapter, i) => {
                const end = call.chapters[i + 1]?.start ?? call.duration;
                const segment = document.createElement('span');
                segment.dataset.tip = chapter.title;
                segment.style.flexGrow = String(Math.max(1, end - chapter.start));
                segment.append(document.createElement('i'));
                segment.addEventListener('click', (event) => {
                    const box = segment.getBoundingClientRect();
                    const share = Math.min(1, Math.max(0, (event.clientX - box.left) / box.width));
                    audio.currentTime = chapter.start + share * (end - chapter.start);
                    if (audio.paused) audio.play().catch(() => {});
                });
                return segment;
            }));
            track.setAttribute('aria-valuemax', String(Math.round(call.duration)));
        } else if (at !== undefined) {
            audio.currentTime = at;
        }
        dock.hidden = false;
        document.body.classList.add('has-dock');
        alignTips(dock);
        buildDrawer(call);
        if ('mediaSession' in navigator) {
            navigator.mediaSession.metadata = new MediaMetadata({ title: call.title, artist: 'The Axis of Stevil Show', album: call.label, artwork: [{ src: 'https://archive.axisofstevil.com/earnings-calls/cover.jpg', sizes: '3000x3000', type: 'image/jpeg' }] });
        }
        return true;
    }

    function play(slug, at) {
        if (load(slug, at)) audio.play().catch(() => {});
    }

    // The page's own call, bound to the dock whenever a page arrives.
    // A tooltip opens over its segment, but one near either end of its bar anchors inward, so it never runs past the
    // bar's edge (the first and last are anchored by the stylesheet; this takes the outer thirds).
    function alignTips(root = document) {
        root.querySelectorAll('.timeline, [data-track]').forEach((bar) => {
            const box = bar.getBoundingClientRect();
            if (!box.width) return;
            bar.querySelectorAll('[data-tip]').forEach((tip) => {
                const r = tip.getBoundingClientRect();
                const at = (r.left + r.width / 2 - box.left) / box.width;
                tip.dataset.tipAlign = at < 1 / 3 ? 'start' : at > 2 / 3 ? 'end' : '';
            });
        });
    }

    // Narrow windows keep the dock ready on a call's page (bindPage); closed there, it stays away for that visit.
    const narrow = matchMedia('(max-width: 1100px)');
    let dismissedOn = null;

    function bindPage() {
        zoomView = null;
        setTimeout(alignTips, 0);
        setTimeout(offerZoom, 0);
        const call = document.querySelector('[data-call]');
        dock.classList.toggle('is-playing', !audio.paused);
        document.body.classList.toggle('has-dock', !dock.hidden);
        markResumes();
        if (!call) return;
        // Phones and tablets: on a call's page the dock is there from the start, ready with this call (paused, at the
        // listener's place), so the call and its exhibits are always in reach. A call already in the dock stays;
        // closing the dock puts it away for the rest of this visit to the page.
        if (narrow.matches && dock.hidden && dismissedOn !== location.pathname) {
            const saved = recall(call.dataset.call);
            if (saved > 0) audio.preload = 'metadata';
            load(call.dataset.call, saved);
            dock.querySelector('[data-at]').textContent = clock(saved);
        }
        const slug = call.dataset.call;
        const ours = () => current && current.slug === slug;

        call.querySelector('[data-play]')?.addEventListener('click', () => {
            if (ours() && !audio.paused) audio.pause(); else play(slug);
        });
        // The timeline, the agenda and the remarks all seek there.
        document.querySelectorAll('[data-seek]').forEach((button) => button.addEventListener('click', () => play(slug, Number(button.dataset.seek))));
        call.querySelectorAll('[data-exhibit-tab]').forEach((tab) => tab.addEventListener('click', () => {
            const number = Number(tab.dataset.exhibitTab);
            showExhibit(call, number);
            const chapter = (bySlug[slug]?.chapters || []).find((c) => c.exhibit === number);
            if (chapter && ours()) audio.currentTime = chapter.start;
        }));
        let landing = null;
        try { landing = sessionStorage.getItem('show.to-spot'); sessionStorage.removeItem('show.to-spot'); } catch (e) {}
        if (landing && landing.split('|')[0] === slug) setTimeout(() => { paint(); scrollToSpot(false); if (landing.endsWith('|follow')) setFollowing(true); }, 60);
        // Not yet loaded but begun before: the console shows where the listener left off.
        const saved = recall(slug);
        const call_ = bySlug[slug];
        if (!ours() && call_ && inProgress(call_, saved)) {
            call.querySelector('[data-clock]').textContent = clock(saved);
            const topic = call_.chapters[chapterAt(call_, saved)];
            if (topic) call.querySelector('[data-now-title]').textContent = topic.title;
        }
        // The small copy shows while the packet is out of view; a click goes back up to it.
        const packet = call.querySelector('[data-packet]');
        const mini = document.querySelector('[data-mini-packet]');
        const slot = document.querySelector('[data-mini-slot]');
        if (packet && mini && slot && 'IntersectionObserver' in window) {
            // Shown once the packet is mostly gone above, hidden again as it comes back: a margin either way, so it never flickers.
            new IntersectionObserver(([entry]) => {
                const shown = !entry.isIntersecting && entry.boundingClientRect.top < 0;
                slot.classList.toggle('is-shown', shown);
                mini.setAttribute('aria-hidden', String(!shown));
                mini.tabIndex = shown ? 0 : -1;
                const zoom = mini.querySelector('[data-mini-zoom]');
                if (zoom) zoom.tabIndex = shown ? 0 : -1;
            }, { rootMargin: '-35% 0px 0px 0px' }).observe(packet);
            // Its Zoom opens the exhibit it shows, full size; anywhere else on it goes back up to the packet.
            const chip = mini.querySelector('[data-mini-zoom]');
            mini.dataset.exhibit ||= '1';
            const zoomMini = (event) => { event.preventDefault(); event.stopPropagation(); if (mini.dataset.exhibit) openZoom(mini.dataset.exhibit); };
            chip?.addEventListener('click', zoomMini);
            chip?.addEventListener('keydown', (event) => { if (event.key === 'Enter' || event.key === ' ') zoomMini(event); });
            mini.addEventListener('click', (event) => { event.preventDefault(); packet.scrollIntoView({ behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'center' }); });
        }
        paint();
    }

    // The notes' small copy of the packet: what the packet shows, mirrored.
    function mirror(image, number, title, exhibit) {
        const mini = document.querySelector('[data-mini-packet]');
        if (!mini) return;
        mini.dataset.exhibit = exhibit ?? '';
        const chip = mini.querySelector('[data-mini-zoom]');
        if (chip) chip.hidden = !exhibit;
        const img = mini.querySelector('[data-mini-image]');
        if (img.getAttribute('src') !== image.src) { img.src = image.src; img.alt = image.alt; }
        mini.querySelector('[data-mini-number]').textContent = number;
        mini.querySelector('[data-mini-title]').textContent = title;
    }

    // While a chapter with no exhibit plays, the packet shows the chapter's own picture (its plate).
    function showChapter(call, chapter) {
        const frame = call.querySelector('[data-chapter-frame]');
        if (!frame) return;
        call.querySelectorAll('[data-exhibit]').forEach((other) => { other.hidden = true; });
        const image = frame.querySelector('[data-chapter-image]');
        if (image.getAttribute('src') !== chapter.image) image.src = chapter.image;
        frame.querySelector('[data-chapter-caption]').textContent = chapter.title;
        mirror({ src: chapter.image, alt: '' }, 'No exhibit', chapter.title, null);
        if (frame.hidden) { frame.hidden = false; frame.classList.remove('is-turning'); void frame.offsetWidth; frame.classList.add('is-turning'); }
        call.querySelectorAll('[data-exhibit-tab]').forEach((tab) => tab.setAttribute('aria-pressed', 'false'));
    }

    function showExhibit(call, number) {
        const frames = call.querySelectorAll('[data-exhibit]');
        if (!frames.length) return;
        const chapterFrame = call.querySelector('[data-chapter-frame]');
        if (chapterFrame) chapterFrame.hidden = true;
        frames.forEach((frame) => {
            const on = Number(frame.dataset.exhibit) === number;
            if (on && frame.hidden) { frame.hidden = false; frame.classList.remove('is-turning'); void frame.offsetWidth; frame.classList.add('is-turning'); }
            if (on) setTimeout(offerZoom, 0);
            if (!on) frame.hidden = true;
        });
        call.querySelectorAll('[data-exhibit-tab]').forEach((tab) => tab.setAttribute('aria-pressed', String(Number(tab.dataset.exhibitTab) === number)));
        const shown = call.querySelector('[data-exhibit="' + number + '"]');
        const picture = shown?.querySelector('img');
        if (picture) mirror({ src: picture.currentSrc || picture.src, alt: picture.alt }, 'Exhibit ' + number + ' of ' + frames.length, shown.dataset.title || '', String(number));
    }

    // In progress: past the hold and the welcome (into the call's first item), and not yet at its forward-looking
    // statements (its close). Hearing the introduction is not starting a call; hearing its sign-off is finishing it.
    function inProgress(call, at) {
        const titles = call.chapters.map((chapter) => chapter.title);
        const welcome = titles.indexOf('Welcome');
        const begun = call.chapters[welcome + 1]?.start ?? 90;
        const close = call.chapters.find((chapter) => /^Forward-looking/i.test(chapter.title))?.start ?? call.duration - 60;
        return at >= begun && at < close;
    }

    // A quarter reached by a link from another page: Turbo's visit doesn't set :target, so it is marked here (the
    // call keeps playing; a full load would stop it). The thumb index's own jumps are the browser's, and use :target.
    function markTarget() {
        document.querySelectorAll('.is-target').forEach((el) => el.classList.remove('is-target'));
        const id = decodeURIComponent(location.hash.slice(1));
        const target = id && document.getElementById(id);
        if (target) { target.classList.add('is-target'); target.scrollIntoView({ block: 'start' }); }
    }
    window.addEventListener('hashchange', () => document.querySelectorAll('.is-target').forEach((el) => el.classList.remove('is-target')));

    // A call's play buttons, wherever they are: resume (with how much has been heard) when there is a saved place in it.
    function markResumes() {
        document.querySelectorAll('[data-call-slug]').forEach((button) => {
            const call = bySlug[button.dataset.callSlug];
            if (!call) return;
            const at = current && current.slug === call.slug ? audio.currentTime : recall(call.slug);
            const resumable = inProgress(call, at);
            button.classList.toggle('has-resume', resumable);
            button.style.setProperty('--heard', resumable ? ((at / call.duration) * 100).toFixed(1) + '%' : '0%');
            const playing = current && current.slug === call.slug && !audio.paused;
            const label = playing ? 'Pause ' + call.title : resumable ? 'Resume ' + call.title + ' at ' + clock(at) : 'Play ' + call.title;
            button.setAttribute('aria-label', label);
            button.title = label;
        });
    }

    // Everything that follows the audio: the dock, and the page's console if it is this call.
    // The exhibits drawer (phones and tablets): the packet, risen from the dock wherever the listener is. It follows the
    // call, chapter by chapter; an exhibit picked by hand stays until the call moves on, or until "Follow the call".
    const drawer = dock.querySelector('[data-drawer]');
    const drawerOpener = dock.querySelector('[data-drawer-open]');
    let drawerPick = null;
    let drawerChapter = -1;
    let drawerShown = '';

    function buildDrawer(call) {
        drawerPick = null; drawerChapter = -1; drawerShown = '';
        const tabs = drawer.querySelector('[data-drawer-tabs]');
        tabs.replaceChildren(...(call.exhibits || []).map((exhibit) => {
            const tab = document.createElement('button');
            tab.type = 'button';
            tab.className = 'packet-tab';
            tab.textContent = String(exhibit.number);
            tab.dataset.drawerTab = String(exhibit.number);
            tab.setAttribute('aria-label', 'Exhibit ' + exhibit.number);
            tab.setAttribute('aria-pressed', 'false');
            tab.addEventListener('click', () => { drawerPick = exhibit.number; renderDrawer(true); });
            return tab;
        }));
        tabs.hidden = !(call.exhibits || []).length;
    }

    function renderDrawer(force) {
        if (!current) return;
        const index = chapterAt(current, audio.currentTime || 0);
        if (index !== drawerChapter) { drawerChapter = index; drawerPick = null; }
        const chapter = current.chapters[index];
        const exhibits = current.exhibits || [];
        const number = drawerPick ?? chapter?.exhibit ?? null;
        const exhibit = number ? exhibits.find((x) => x.number === number) : null;
        const key = current.slug + '|' + index + '|' + (number ?? '');
        if (!force && key === drawerShown) return;
        drawerShown = key;
        const image = exhibit ? exhibit.image : (chapter?.image || '');
        const label = exhibit ? 'Exhibit ' + exhibit.number + ' of ' + exhibits.length : 'No exhibit';
        const title = exhibit ? exhibit.title : (chapter?.title || '');
        dock.querySelector('[data-drawer-badge]').textContent = exhibit ? 'Exhibit ' + exhibit.number : 'Exhibits';
        drawerOpener.setAttribute('aria-label', (exhibit ? label + ': ' + title : 'The exhibits') + '. Open the exhibits');
        const picture = drawer.querySelector('[data-drawer-image]');
        // Fetched only while the drawer is open: closed, it waits (the page's own pictures come first).
        if (image && drawer.hidden) {
            picture.dataset.src = image;
        } else if (image && picture.getAttribute('src') !== image) {
            picture.src = image;
            picture.parentElement.classList.remove('is-turning'); void picture.parentElement.offsetWidth; picture.parentElement.classList.add('is-turning');
        }
        picture.alt = exhibit ? exhibit.alt : '';
        drawer.dataset.exhibit = exhibit ? String(exhibit.number) : '';
        offerDrawerZoom();
        drawer.querySelector('[data-drawer-number]').textContent = label;
        drawer.querySelector('[data-drawer-title]').textContent = title;
        const work = drawer.querySelector('[data-drawer-work]');
        work.hidden = !exhibit?.work;
        if (exhibit?.work) work.href = exhibit.work;
        drawer.querySelectorAll('[data-drawer-tab]').forEach((tab) => tab.setAttribute('aria-pressed', String(Number(tab.dataset.drawerTab) === number)));
        drawer.querySelector('[data-drawer-follow]').hidden = drawerPick === null || drawerPick === (chapter?.exhibit ?? null);
    }

    // The drawer's Zoom: only for an exhibit (not a chapter's plate) shown meaningfully smaller than its file, as the
    // packet's; it opens the viewer on the player's call, at this exhibit.
    function offerDrawerZoom() {
        const picture = drawer.querySelector('.drawer-picture');
        const img = picture.querySelector('img');
        const chip = picture.querySelector('[data-drawer-zoom]');
        const bigger = !!drawer.dataset.exhibit && img.complete && img.clientWidth > 0 && (img.naturalWidth > img.clientWidth * 1.15 || img.naturalHeight > img.clientHeight * 1.15);
        chip.hidden = !bigger;
        picture.classList.toggle('can-zoom', bigger);
    }
    const zoomFromDrawer = () => { if (drawer.dataset.exhibit && current) openZoom(drawer.dataset.exhibit, current.slug); };
    drawer.querySelector('[data-drawer-zoom]').addEventListener('click', zoomFromDrawer);
    drawer.querySelector('[data-drawer-image]').addEventListener('click', () => { if (drawer.querySelector('.drawer-picture').classList.contains('can-zoom')) zoomFromDrawer(); });
    drawer.querySelector('[data-drawer-image]').addEventListener('load', offerDrawerZoom);

    function openDrawer() {
        drawer.hidden = false;
        renderDrawer(true);
        drawerOpener.setAttribute('aria-expanded', 'true');
        requestAnimationFrame(() => drawer.classList.add('is-open'));
        setTimeout(offerDrawerZoom, 300);
        drawer.querySelector('[data-drawer-close]').focus({ preventScroll: true });
    }

    function closeDrawer(returnFocus = true) {
        if (drawer.hidden) return;
        drawer.classList.remove('is-open');
        drawerOpener.setAttribute('aria-expanded', 'false');
        const done = () => { if (!drawer.classList.contains('is-open')) drawer.hidden = true; };
        matchMedia('(prefers-reduced-motion: reduce)').matches ? done() : setTimeout(done, 260);
        if (returnFocus) drawerOpener.focus({ preventScroll: true });
    }

    drawerOpener.addEventListener('click', () => (drawer.hidden ? openDrawer() : closeDrawer()));
    drawer.querySelector('[data-drawer-close]').addEventListener('click', () => closeDrawer());
    // Following again hides the button pressed, so focus goes on to the drawer's close, not off into the page.
    drawer.querySelector('[data-drawer-follow]').addEventListener('click', () => { drawerPick = null; renderDrawer(true); drawer.querySelector('[data-drawer-close]').focus({ preventScroll: true }); });
    // Escape puts it away wherever focus is, while it is open.
    // (Not while the full-size viewer is open over it: Escape closes only the top layer.)
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && !drawer.hidden && drawer.classList.contains('is-open') && !document.querySelector('.zoom-view[open]')) closeDrawer(); });
    // A swipe down on its head puts it away, as a sheet does.
    let grabbed = null;
    const grab = drawer.querySelector('[data-drawer-grab]');
    grab.addEventListener('pointerdown', (event) => { if (event.target.closest('button')) return; grabbed = event.clientY; grab.setPointerCapture(event.pointerId); });
    grab.addEventListener('pointerup', (event) => { if (grabbed !== null && event.clientY - grabbed > 50) closeDrawer(); grabbed = null; });
    grab.addEventListener('pointercancel', () => { grabbed = null; });
    // The dock's position slider, by keyboard: arrows move 15 seconds, Page Up and Down a minute, Home and End the ends.
    dock.querySelector('[data-track]').addEventListener('keydown', (event) => {
        if (!current) return;
        const steps = { ArrowRight: 15, ArrowUp: 15, ArrowLeft: -15, ArrowDown: -15, PageUp: 60, PageDown: -60 };
        let to = null;
        if (event.key in steps) to = audio.currentTime + steps[event.key];
        else if (event.key === 'Home') to = 0;
        else if (event.key === 'End') to = Math.max(0, current.duration - 1);
        if (to === null) return;
        event.preventDefault();
        audio.currentTime = Math.min(Math.max(0, to), current.duration);
        paint();
    });

    // The page keeps room for the dock at its foot: the dock's own height, measured, whatever its rows.
    if ('ResizeObserver' in window) new ResizeObserver(() => { if (!dock.hidden) document.body.style.setProperty('--dock', Math.ceil(dock.getBoundingClientRect().height) + 'px'); }).observe(dock);

    // The drawer is for narrow windows: widened past them, it closes.
    matchMedia('(min-width: 1101px)').addEventListener('change', (event) => { if (event.matches) closeDrawer(false); });
    // To the spot puts the drawer away, so the spot it scrolls to is in view.
    dock.querySelector('[data-to-spot]').addEventListener('click', () => closeDrawer(false));

    let lastChapter = -1;
    function paint() {
        const at = audio.currentTime || 0;
        markResumes();
        document.querySelectorAll('[data-play-call]').forEach((button) => button.classList.toggle('is-current', !!current && current.slug === button.dataset.playCall && !audio.paused));
        dock.querySelector('[data-at]').textContent = clock(at);
        const call = document.querySelector('[data-call]');
        if (!current) return;
        const index = chapterAt(current, at);
        const chapter = current.chapters[index];
        dock.querySelector('[data-dock-now]').textContent = chapter ? chapter.title : '';
        renderDrawer(false);
        const track = dock.querySelector('[data-track]');
        track.setAttribute('aria-valuenow', String(Math.round(at)));
        track.setAttribute('aria-valuetext', clock(at) + ' of ' + clock(current.duration) + (chapter ? ', ' + chapter.title : ''));
        dock.querySelectorAll('[data-track] > span').forEach((segment, i) => {
            const start = current.chapters[i].start;
            const end = current.chapters[i + 1]?.start ?? current.duration;
            const fill = at >= end ? 100 : at <= start ? 0 : ((at - start) / Math.max(1, end - start)) * 100;
            segment.classList.toggle('is-current', i === index);
            segment.querySelector('i').style.setProperty('--fill', fill.toFixed(1) + '%');
        });
        if (!call || call.dataset.call !== current.slug) {
            if (call) call.classList.remove('is-playing');
            return;
        }
        call.classList.toggle('is-playing', !audio.paused);
        call.querySelector('[data-clock]').textContent = clock(at);
        const state = call.querySelector('[data-live-state]');
        if (state) state.textContent = audio.paused ? 'On hold' : 'Live';
        document.querySelectorAll('.agenda-item').forEach((item, i) => {
            item.classList.toggle('is-current', i === index);
            item.classList.toggle('is-done', i < index);
            item.querySelector('button')?.setAttribute('aria-current', i === index ? 'true' : 'false');
        });
        // The prepared remarks for the chapter being spoken, lifted.
        // The last chapter's remarks end where the speaking does: before the outro, the same 12 seconds in every call.
        const chapterEnd = current.chapters[index + 1]?.start ?? Math.max(chapter?.start ?? 0, current.duration - OUTRO);
        const through = chapter ? Math.min(100, Math.max(0, ((at - chapter.start) / Math.max(1, chapterEnd - chapter.start)) * 100)) : 0;
        document.querySelectorAll('[data-remarks-start]').forEach((part) => {
            const on = !!chapter && Math.abs(Number(part.dataset.remarksStart) - chapter.start) < 0.5;
            part.classList.toggle('is-current', on);
            if (on) {
                // The playhead beside the paragraph being spoken, moving down it as it is read (its cue's times);
                // between paragraphs, it rests at the end of the last. Without timed paragraphs, the chapter's share.
                const track = part.querySelector('.remarks-track');
                // The line runs from the part's dot, wherever the layout puts the dot: its top at the dot's centre,
                // on the dot's vertical, so the two read as one mark at every width.
                const dot = part.querySelector('.remarks-now');
                if (track && dot) {
                    const d = dot.getBoundingClientRect(), box = part.getBoundingClientRect();
                    if (d.width) {
                        track.style.top = (d.top + d.height / 2 - box.top).toFixed(1) + 'px';
                        track.style.left = (d.left + d.width / 2 - box.left - track.offsetWidth / 2).toFixed(1) + 'px';
                    }
                }
                const cues = [...part.querySelectorAll('[data-cue]')];
                let progress = through;
                if (track && cues.length) {
                    const said = cues.filter((cue) => Number(cue.dataset.cue) <= at);
                    const cue = said[said.length - 1] || cues[0];
                    speaking = cue;
                    const begin = Number(cue.dataset.cue), end = Number(cue.dataset.cueEnd);
                    const share = said.length ? Math.min(1, Math.max(0, (at - begin) / Math.max(0.1, end - begin))) : 0;
                    const top = track.getBoundingClientRect().top, height = track.getBoundingClientRect().height;
                    const box = cue.getBoundingClientRect();
                    progress = Math.min(100, Math.max(0, ((box.top + box.height * share - top) / Math.max(1, height)) * 100));
                }
                track?.querySelector('i')?.style.setProperty('--progress', progress.toFixed(1) + '%');
            }
        });
        // Following: the playhead kept in the middle band of the window above the dock.
        if (following && !audio.paused) {
            const head = spot();
            if (head && Date.now() - steering > 1200) {
                const y = spotY(head);
                if (y < seen() * 0.2 || y > seen() * 0.7) scrollToSpot(true);
            }
        }
        // The call's chart: the chapters before this one full, this one filling.
        const next = current.chapters[index + 1]?.start ?? current.duration;
        call.querySelectorAll('.timeline li').forEach((item, i) => {
            item.classList.toggle('is-current', i === index);
            item.classList.toggle('is-done', i < index);
            const fill = i < index ? 100 : i > index ? 0 : Math.min(100, ((at - chapter.start) / Math.max(1, next - chapter.start)) * 100);
            item.querySelector('i')?.style.setProperty('--fill', fill.toFixed(1) + '%');
        });
        if (chapter) {
            call.querySelector('[data-now-title]').textContent = chapter.title;
            // The packet follows the call once it is under way; until then it shows its first exhibit, as the page
            // arrived (a ready dock at 0:00 doesn't turn it to the hold's plate).
            if (index !== lastChapter && (!audio.paused || audio.currentTime > 0.5)) {
                if (chapter.exhibit) showExhibit(call, chapter.exhibit); else showChapter(call, chapter);
                lastChapter = index;
            }
        }
    }

    audio.addEventListener('timeupdate', () => { paint(); if (current) remember(current.slug, audio.currentTime); });
    audio.addEventListener('play', () => { dock.classList.add('is-playing'); dock.querySelector('[data-toggle]').setAttribute('aria-label', 'Pause'); paint(); });
    audio.addEventListener('pause', () => { dock.classList.remove('is-playing'); dock.querySelector('[data-toggle]').setAttribute('aria-label', 'Play'); paint(); });
    // Autoplay: on by default; remembered in this browser.
    const pref = (key, fallback) => { try { const v = localStorage.getItem(key); return v === null ? fallback : v === '1'; } catch (e) { return fallback; } };
    const setPref = (key, on) => { try { localStorage.setItem(key, on ? '1' : '0'); } catch (e) {} };
    const autoplay = dock.querySelector('[data-autoplay]');
    autoplay.checked = pref('show.autoplay', true);
    autoplay.addEventListener('change', () => setPref('show.autoplay', autoplay.checked));

    // A call ends: with autoplay, the next in the run begins, from its top.
    audio.addEventListener('ended', () => {
        if (!current) return;
        remember(current.slug, 0);
        if (!autoplay.checked) return;
        const i = playlist.findIndex((call) => call.slug === current.slug);
        if (playlist[i + 1]) { lastChapter = -1; play(playlist[i + 1].slug, 0); }
    });

    dock.querySelector('[data-toggle]').addEventListener('click', () => (audio.paused ? audio.play().catch(() => {}) : audio.pause()));
    dock.querySelector('[data-close]').addEventListener('click', () => {
        dismissedOn = location.pathname;
        closeDrawer(false);
        audio.pause();
        dock.hidden = true;
        document.body.classList.remove('has-dock');
    });
    if ('mediaSession' in navigator) {
        navigator.mediaSession.setActionHandler('play', () => audio.play());
        navigator.mediaSession.setActionHandler('pause', () => audio.pause());
        navigator.mediaSession.setActionHandler('seekbackward', () => { audio.currentTime = Math.max(0, audio.currentTime - 15); });
        navigator.mediaSession.setActionHandler('seekforward', () => { audio.currentTime += 15; });
    }

    // Zoom: the exhibit shown full size, over the page (the call plays on in the dock); its arrows step through the
    // call's exhibits. Offered only where the file is meaningfully bigger than the picture as shown.
    let zoomView = null;
    let zoomAt = 0;
    // The exhibits the viewer pages through: the call's, as the player carries them (the playlist), whichever opened it:
    // the packet, the notes' small copy, or the dock's drawer, on any page.
    let zoomCall = null;
    let zoomOpener = null;
    const zoomSet = () => (bySlug[zoomCall]?.exhibits || []).map((x) => ({ src: x.image, alt: x.alt || '', number: String(x.number), title: x.title || '' }));
    function zoomTo(i) {
        const set = zoomSet();
        if (!set.length) return;
        zoomAt = (i + set.length) % set.length;
        const item = set[zoomAt];
        const img = zoomView.querySelector('.zoom-stage img');
        img.src = item.src;
        img.alt = item.alt;
        zoomView.querySelector('.zoom-caption').innerHTML = '';
        const label = document.createElement('b');
        label.textContent = 'Exhibit ' + item.number + ' of ' + set.length;
        zoomView.querySelector('.zoom-caption').append(label, document.createTextNode(item.title));
        zoomView.querySelectorAll('.zoom-step').forEach((step) => { step.hidden = set.length < 2; });
    }
    function openZoom(number, slug) {
        zoomCall = slug || document.querySelector('[data-call]')?.dataset.call || current?.slug || null;
        if (!zoomSet().length) return;
        zoomOpener = document.activeElement;
        if (!zoomView) {
            zoomView = document.createElement('dialog');
            zoomView.className = 'zoom-view';
            zoomView.setAttribute('aria-label', 'The exhibit, full size');
            const arrow = (d) => '<svg viewBox="0 0 16 16" aria-hidden="true"><path d="' + d + '"/></svg>';
            zoomView.innerHTML = '<div class="zoom-stage"><img alt=""></div>'
                + '<button type="button" class="zoom-step" data-step="-1" aria-label="Previous exhibit">' + arrow('M10 3 5 8l5 5') + '</button>'
                + '<button type="button" class="zoom-step" data-step="1" aria-label="Next exhibit">' + arrow('M6 3l5 5-5 5') + '</button>'
                + '<p class="zoom-caption" aria-live="polite"></p>'
                + '<button type="button" class="zoom-close">Close</button>';
            document.body.append(zoomView);
            zoomView.querySelector('.zoom-close').addEventListener('click', () => zoomView.close());
            zoomView.querySelector('.zoom-stage').addEventListener('click', (event) => { if (event.target.classList.contains('zoom-stage')) zoomView.close(); });
            zoomView.querySelectorAll('.zoom-step').forEach((step) => step.addEventListener('click', () => zoomTo(zoomAt + Number(step.dataset.step))));
            // Closed, it hands focus back to what opened it (the drawer's chip, the packet's).
            zoomView.addEventListener('close', () => { if (zoomOpener?.isConnected) zoomOpener.focus({ preventScroll: true }); });
            zoomView.addEventListener('keydown', (event) => {
                if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') { event.preventDefault(); zoomTo(zoomAt + (event.key === 'ArrowLeft' ? -1 : 1)); }
            });
        }
        const set = zoomSet();
        zoomTo(Math.max(0, set.findIndex((item) => item.number === String(number))));
        zoomView.showModal();
        zoomView.querySelector('.zoom-close').focus();
    }
    // Each exhibit's Zoom chip, shown only when its file is meaningfully bigger than it is shown.
    function offerZoom() {
        document.querySelectorAll('.packet-frame[data-exhibit]').forEach((frame) => {
            const picture = frame.querySelector('.packet-picture');
            const img = picture?.querySelector('img');
            if (!img) return;
            let chip = picture.querySelector('.zoom-open');
            if (!chip) {
                chip = document.createElement('button');
                chip.type = 'button';
                chip.className = 'zoom-open';
                chip.hidden = true;
                chip.innerHTML = '<svg viewBox="0 0 12 12" aria-hidden="true"><circle cx="5" cy="5" r="3.5" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M7.6 7.6 11 11" stroke="currentColor" stroke-width="1.5"/></svg>Zoom';
                chip.setAttribute('aria-label', 'Zoom: view this exhibit full size');
                chip.addEventListener('click', () => openZoom(frame.dataset.exhibit));
                img.addEventListener('click', () => { if (picture.classList.contains('can-zoom')) openZoom(frame.dataset.exhibit); });
                img.addEventListener('load', offerZoom);
                picture.append(chip);
            }
            const bigger = img.complete && img.clientWidth > 0 && (img.naturalWidth > img.clientWidth * 1.15 || img.naturalHeight > img.clientHeight * 1.15);
            chip.hidden = !bigger;
            picture.classList.toggle('can-zoom', bigger);
        });
    }

    // The spot in the transcript: the playhead of the part being spoken. To the spot scrolls there (from another page,
    // it opens the call's page and lands there, the call playing on); following keeps it in view, until the listener
    // scrolls away themself.
    // Following is the dock's To the spot at work: on when pressed, off at the listener's own scroll; never remembered.
    let following = false;
    let steering = 0;
    let steered = false;
    let steerTimer = null;
    let pushed = 0;
    let pushedAt = 0;
    window.addEventListener('scrollend', () => { if (steered) { clearTimeout(steerTimer); steerTimer = setTimeout(() => { steered = false; }, 250); } });
    // The spot: the playhead where it is laid out; else the paragraph being spoken; else the part. (A hidden element
    // measures at zero, which is the top of the window, not the spot.)
    let speaking = null;
    const spot = () => {
        const head = document.querySelector('.remarks.is-current .remarks-track i');
        if (head && head.getClientRects().length) return head;
        if (speaking && speaking.isConnected && speaking.closest('.remarks.is-current')) return speaking;
        return document.querySelector('.remarks.is-current');
    };
    const spotY = (target) => { const box = target.getBoundingClientRect(); return target.matches('i') ? box.bottom : box.top; };
    // The window as the listener sees it: above the dock, which covers its foot.
    const seen = () => window.innerHeight - (dock.hidden ? 0 : dock.getBoundingClientRect().height);
    function scrollToSpot(smooth) {
        const target = spot();
        if (!target) return false;
        const y = window.scrollY + spotY(target) - seen() * 0.4;
        steering = Date.now();
        steered = true;
        clearTimeout(steerTimer);
        // Until the page's own scroll has ended (scrollend; a timeout where that isn't known), nothing counts as the listener's.
        steerTimer = setTimeout(() => { steered = false; }, 2000);
        window.scrollTo({ top: Math.max(0, y), behavior: smooth && !matchMedia('(prefers-reduced-motion: reduce)').matches ? 'smooth' : 'auto' });
        return true;
    }
    function setFollowing(on) {
        following = on;
        const button = dock.querySelector('[data-to-spot]');
        button.classList.toggle('is-following', on);
        button.setAttribute('aria-pressed', String(on));
        button.querySelector('span').textContent = on ? 'Following' : 'To the spot';
    }
    // To the spot (the dock's): there, and following, until the listener's own scroll.
    function toSpot(follow) {
        if (!current) return;
        const onIts = document.querySelector('[data-call]')?.dataset.call === current.slug;
        if (onIts) { paint(); scrollToSpot(true); if (follow) setFollowing(true); return; }
        try { sessionStorage.setItem('show.to-spot', current.slug + (follow ? '|follow' : '')); } catch (e) {}
        if (window.Turbo) window.Turbo.visit(current.url); else location.href = current.url;
    }
    dock.querySelector('[data-to-spot]').addEventListener('click', () => toSpot(true));
    // The listener's own, deliberate scrolling ends following: about 150px of wheel or swipe within half a second, and
    // never while the page's own scroll (to the spot, following) is still moving or settling.
    const pushedBy = (amount) => {
        if (!following || steered) { pushed = 0; return; }
        const now = Date.now();
        if (now - pushedAt > 500) pushed = 0;
        pushedAt = now;
        pushed += Math.abs(amount);
        if (pushed > 150) { pushed = 0; setFollowing(false); }
    };
    window.addEventListener('wheel', (event) => pushedBy(event.deltaY), { passive: true });
    let touchY = null;
    window.addEventListener('touchstart', (event) => { touchY = event.touches[0]?.clientY ?? null; }, { passive: true });
    window.addEventListener('touchmove', (event) => { const y = event.touches[0]?.clientY; if (touchY !== null && y !== undefined) { pushedBy(y - touchY); touchY = y; } }, { passive: true });
    window.addEventListener('keydown', (event) => { if (following && ['ArrowUp', 'ArrowDown', 'PageUp', 'PageDown', 'Home', 'End', ' '].includes(event.key) && event.target === document.body) setFollowing(false); });

    // A call's play button anywhere but its own page: the call starts in the dock, then its page opens around it.
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-play-call]');
        if (!button) return;
        const slug = button.dataset.playCall;
        if (current && current.slug === slug && !audio.paused) { audio.pause(); return; }
        play(slug);
        if (window.Turbo && location.pathname !== button.dataset.href) window.Turbo.visit(button.dataset.href);
    });

    // The feed's address, copied on a click; the button says so for a moment.
    document.addEventListener('click', (event) => {
        const target = event.target.closest('[data-copy]');
        if (!target || !navigator.clipboard) return;
        navigator.clipboard.writeText(target.dataset.copy || target.textContent.trim()).then(() => {
            // The label, not the button, says so: the button's icon stays.
            const text = target.querySelector('.app-go-label') || target;
            const label = text.textContent;
            target.classList.add('is-copied');
            text.textContent = 'Copied';
            setTimeout(() => { target.classList.remove('is-copied'); text.textContent = label; }, 1400);
        }).catch(() => {});
    });

    // The breadcrumb's switchers, as the archive's: one open at a time, closed on Escape or a click elsewhere.
    document.addEventListener('toggle', (event) => {
        const opened = event.target;
        if (opened.matches?.('.switcher') && opened.open) document.querySelectorAll('.switcher[open]').forEach((other) => other !== opened && (other.open = false));
    }, true);
    document.addEventListener('keydown', (event) => {
        const open = event.key === 'Escape' && event.target.closest?.('.switcher[open]');
        if (open) { open.open = false; open.querySelector('summary').focus(); }
    });
    document.addEventListener('click', (event) => {
        document.querySelectorAll('.switcher[open]').forEach((switcher) => { if (!switcher.contains(event.target)) switcher.open = false; });
    });

    // The subscribe page: the apps that run on this device first, marked so.
    function markApps() {
        const list = document.querySelector('[data-apps]');
        if (!list) return;
        const ua = navigator.userAgent;
        const touchMac = /Macintosh/.test(ua) && navigator.maxTouchPoints > 1;
        const platform = /iPhone|iPad|iPod/.test(ua) || touchMac ? 'ios' : /Android/.test(ua) ? 'android' : /Macintosh/.test(ua) ? 'mac' : /Windows/.test(ua) ? 'windows' : null;
        if (!platform) return;
        const rows = [...list.children];
        const here = rows.filter((row) => row.dataset.platforms.split(' ').includes(platform));
        here.forEach((row) => row.classList.add('is-here'));
        [...here, ...rows.filter((row) => !here.includes(row))].forEach((row) => list.appendChild(row));
    }

    // Search, as the archive's: the dialog in every page (master), opened from the running head's button or ⌘K (Ctrl+K),
    // closed by Esc, its button or the backdrop. The index (Pagefind, built over the finished pages into /pagefind/) loads
    // when first asked; what is typed is searched as it is typed, and each result shows its title, what it is, a line of
    // its words and its picture. The dialog is in the body, which Turbo swaps, so each visit binds its own.
    let pagefindLoading = null;
    let searchOpen = () => {};

    function startSearch() {
        const dialog = document.querySelector('[data-search]');
        if (!dialog) return;
        const input = dialog.querySelector('.search-input');
        const status = dialog.querySelector('[data-search-status]');
        const list = dialog.querySelector('[data-search-results]');
        const open = () => {
            if (!dialog.open) dialog.showModal();
            input.focus();
            input.select();
        };
        const load = () => (pagefindLoading ??= import('/pagefind/pagefind.js').then(async (pagefind) => {
            await pagefind.init();
            return pagefind;
        }));
        let latest = 0;

        const render = async (term) => {
            const run = ++latest;
            // An emptied field clears at once and the dialog closes up to the field; otherwise the last results stay in
            // place until the new ones arrive, the dialog holding its size.
            if (term.trim() === '') {
                list.replaceChildren();
                status.textContent = '';
                dialog.classList.remove('is-showing');
                return;
            }
            const pagefind = await load();
            const found = await pagefind.search(term);
            if (run !== latest) return;
            const results = await Promise.all(found.results.slice(0, 12).map((result) => result.data()));
            if (run !== latest) return;

            status.textContent = found.results.length === 0 ? 'Nothing on record for that.' : `${found.results.length} ${found.results.length === 1 ? 'result' : 'results'}`;
            dialog.classList.add('is-showing');
            list.replaceChildren();
            results.forEach((result) => {
                const item = document.createElement('li');
                const link = document.createElement('a');
                link.href = result.url;
                link.className = 'search-result';
                if (result.meta.image) {
                    const picture = document.createElement('img');
                    picture.src = result.meta.image;
                    picture.alt = '';
                    picture.loading = 'lazy';
                    picture.className = 'search-picture';
                    link.append(picture);
                }
                const words = document.createElement('span');
                words.className = 'search-words';
                const title = document.createElement('strong');
                title.className = 'search-result-title';
                title.textContent = result.meta.title || result.url;
                const what = document.createElement('span');
                what.className = 'search-what';
                what.textContent = [result.meta.kind, result.meta.quarter].filter(Boolean).join(' · ');
                const excerpt = document.createElement('span');
                excerpt.className = 'search-excerpt';
                excerpt.innerHTML = result.excerpt;
                words.append(title, what, excerpt);
                link.append(words);
                item.append(link);
                list.append(item);
            });
        };

        let waiting = 0;
        input.addEventListener('input', () => {
            clearTimeout(waiting);
            waiting = setTimeout(() => render(input.value), 250);
        });
        dialog.querySelector('.search-form').addEventListener('submit', (event) => {
            event.preventDefault();
            list.querySelector('a')?.click();
        });
        dialog.querySelector('[data-search-close]').addEventListener('click', () => dialog.close());
        // Esc closes at once, even from the field, which would otherwise spend the first press clearing itself.
        dialog.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') { event.preventDefault(); dialog.close(); }
        });
        // A click on the backdrop (outside the dialog's box) closes it; a result followed closes it too, before Turbo
        // swaps the page under it.
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog || event.target.closest('.search-result')) dialog.close();
        });
        document.querySelectorAll('[data-search-open]').forEach((button) => button.addEventListener('click', open));
        searchOpen = open;
    }

    document.addEventListener('keydown', (event) => {
        if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            searchOpen();
        }
    });

    document.addEventListener('turbo:load', () => { lastChapter = -1; bindPage(); markApps(); startSearch(); window.axisSnap?.snap(); markTarget(); });
    // The sheet's grid follows the window, and the pictures as they arrive.
    let resizing;
    window.addEventListener('resize', () => { clearTimeout(resizing); resizing = setTimeout(() => { window.axisSnap?.snap(); offerZoom(); alignTips(); }, 120); });
    document.addEventListener('load', (event) => { if (event.target.tagName === 'IMG') window.axisSnap?.snap(); }, true);
    if (!window.Turbo) { bindPage(); markApps(); startSearch(); }
})();
