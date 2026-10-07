<style>
        :root { --paper:#F3F5F7; --ink:#10222B; --teal:#0B6E6E; --teal-dark:#085858; --mint:#DCEFEC;
                --sun:#F0B23A; --line:#D5DCE1; --muted:#5B6B75; }
        body { background:var(--paper); color:var(--ink); font-family:'Public Sans',system-ui,sans-serif; }
        .display { font-family:'Bricolage Grotesque',system-ui,sans-serif; font-weight:700; letter-spacing:-.01em; }
        .muted { color:var(--muted); }
        .panel { background:#fff; border:1px solid var(--line); border-radius:.9rem; }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:.5rem; padding:.7rem 1.2rem;
               border-radius:.6rem; font-weight:600; background:var(--teal); color:#fff; transition:background .15s; cursor:pointer; }
        .btn:hover { background:var(--teal-dark); }
        .btn-quiet { background:transparent; color:var(--ink); border:1px solid var(--line); }
        .btn-quiet:hover { background:#fff; }
        .field { padding:.7rem .9rem; border:1px solid var(--line); border-radius:.6rem; background:#fff; }
        .pill { display:inline-block; padding:.15rem .6rem; border-radius:999px; font-size:.75rem; font-weight:600; background:var(--mint); color:var(--teal-dark); }
        .thumb-fallback { display:grid; place-items:center; background:var(--teal); color:var(--mint);
                          font:700 3rem 'Bricolage Grotesque',sans-serif; }
        :focus-visible { outline:2px solid var(--sun); outline-offset:2px; }
        .track { position:relative; padding-left:2rem; --p:0; }
        .track::before, .track::after { content:''; position:absolute; left:11px; top:10px; width:2px; }
        .track::before { bottom:10px; background:var(--line); }
        .track::after { height:calc((100% - 20px) * var(--p)); background:var(--teal); transition:height .9s cubic-bezier(.2,.8,.2,1); }
        .track-section { margin:1.1rem 0 .3rem; font-weight:600; font-size:.85rem; color:var(--muted); }
        .track-item { position:relative; display:block; padding:.45rem .5rem; border-radius:.5rem; font-size:.95rem; }
        .track-item:hover { background:#fff; }
        .track-item.is-current { background:#fff; font-weight:600; }
        .dot { position:absolute; left:-2rem; top:.5rem; width:24px; height:24px; border-radius:50%; display:grid; place-items:center;
               background:var(--paper); border:2px solid var(--line); color:#fff; }
        .dot.done { background:var(--teal); border-color:var(--teal); }
        .dot.current { border-color:var(--sun); box-shadow:0 0 0 3px rgba(240,178,58,.3); }
        .prose-lms { line-height:1.7; max-width:68ch; }
        .prose-lms h2, .prose-lms h3 { font-family:'Bricolage Grotesque',sans-serif; font-weight:700; margin:1.5rem 0 .5rem; }
        .prose-lms ul { list-style:disc; padding-left:1.25rem; } .prose-lms ol { list-style:decimal; padding-left:1.25rem; }
        .prose-lms a { color:var(--teal); text-decoration:underline; }
        /* scroll reveal: only hides content when JS is running */
        .js .reveal { opacity:0; transform:translateY(16px); transition:opacity .6s ease, transform .6s cubic-bezier(.2,.8,.2,1); }
        .js .reveal.in { opacity:1; transform:none; }
        .bar { height:.5rem; overflow:hidden; border-radius:999px; background:var(--line); }
        .bar > span { display:block; height:100%; background:var(--teal); }
        @media (prefers-reduced-motion: reduce) {
            .track::after { transition:none; }
            .js .reveal { opacity:1; transform:none; transition:none; }
        }

        /* dashboard shell */
        .shell { display:flex; min-height:100vh; }
        .sidebar { position:fixed; inset:0 auto 0 0; width:16.5rem; z-index:40; background:#fff; border-right:1px solid var(--line);
                   display:flex; flex-direction:column; transform:translateX(-100%); transition:transform .25s ease; }
        .sidebar.open { transform:none; }
        .scrim { position:fixed; inset:0; background:rgba(16,34,43,.45); z-index:30; }
        .side-brand { padding:1.15rem 1.25rem; }
        .side-scroll { flex:1; overflow-y:auto; padding:0 .75rem 1rem; }
        .side-label { padding:.9rem .75rem .3rem; font-size:.7rem; letter-spacing:.08em; text-transform:uppercase; color:var(--muted); font-weight:600; }
        .side-link { display:flex; align-items:center; gap:.65rem; padding:.55rem .75rem; border-radius:.55rem; font-size:.92rem; font-weight:500; }
        .side-link:hover { background:var(--paper); }
        .side-link.active { background:var(--mint); color:var(--teal-dark); font-weight:600; }
        .side-user { border-top:1px solid var(--line); padding:.9rem 1rem; display:flex; align-items:center; gap:.7rem; }
        .avatar { width:2.25rem; height:2.25rem; border-radius:50%; display:grid; place-items:center; background:var(--teal); color:#fff; font-weight:600; flex-shrink:0; }
        .content { flex:1; min-width:0; }
        .topbar { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:.75rem 1rem; background:#fff; border-bottom:1px solid var(--line); }
        @media (min-width:1024px) {
            .sidebar { position:sticky; top:0; height:100vh; transform:none; flex-shrink:0; }
            .scrim, .menu-btn { display:none; }
        }
        /* progress ring */
        .ring-bg { stroke:var(--line); } .ring-fg { stroke:var(--teal); transition:none; animation:ring .9s cubic-bezier(.2,.8,.2,1) both; }
        @keyframes ring { from { stroke-dashoffset:var(--c); } }
        .tbl { width:100%; text-align:left; font-size:.875rem; }
        .tbl th { padding:1rem; font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); border-bottom:1px solid var(--line); font-weight:600; }
        .tbl td { padding:1rem; border-bottom:1px solid var(--line); vertical-align:middle; }
        .tbl tr:last-child td { border-bottom:0; }
        .pill-warn { background:#FBEBC8; color:#7A5410; } .pill-grey { background:#E8ECEF; color:#41525C; }
        [x-cloak] { display:none !important; }
        @media (prefers-reduced-motion: reduce) { .sidebar { transition:none; } .ring-fg { animation:none; } }
    

        /* Phase 10: catalogue, course page, lesson player polish */
        .chip { display:inline-flex; align-items:center; padding:.4rem .9rem; border-radius:999px; font-size:.85rem; font-weight:600;
                border:1px solid var(--line); background:#fff; color:var(--ink); cursor:pointer; transition:background .15s, border-color .15s, color .15s; }
        .chip:hover { border-color:var(--teal); }
        .chip.active { background:var(--teal); border-color:var(--teal); color:#fff; }
        .course-card { display:block; overflow:hidden; border-radius:1rem; border:1px solid var(--line); background:#fff;
                       transition:box-shadow .25s ease, transform .25s ease, border-color .25s ease; }
        .course-card:hover { transform:translateY(-3px); border-color:var(--teal); box-shadow:0 12px 28px -14px rgba(11,110,110,.35); }
        .course-card .thumb-wrap { overflow:hidden; }
        .course-card img { transition:transform .5s cubic-bezier(.2,.8,.2,1); }
        .course-card:hover img { transform:scale(1.06); }
        .level-badge { position:absolute; top:.75rem; left:.75rem; backdrop-filter:blur(6px); background:rgba(16,34,43,.55); color:#fff;
                       padding:.25rem .65rem; border-radius:999px; font-size:.72rem; font-weight:600; letter-spacing:.02em; }
        .hero-thumb { position:relative; border-radius:1rem; overflow:hidden; }
        .accordion summary { list-style:none; cursor:pointer; display:flex; align-items:center; justify-content:space-between; }
        .accordion summary::-webkit-details-marker { display:none; }
        .accordion .chev { transition:transform .25s ease; flex-shrink:0; }
        .accordion[open] .chev { transform:rotate(180deg); }
        .accordion .acc-body { display:grid; grid-template-rows:0fr; transition:grid-template-rows .3s ease; }
        .accordion[open] .acc-body { grid-template-rows:1fr; }
        .accordion .acc-inner { overflow:hidden; }
        .enrol-card { transition:box-shadow .25s ease; }
        .enrol-card.stuck { box-shadow:0 16px 32px -18px rgba(16,34,43,.28); }
        .video-frame { border-radius:1rem; overflow:hidden; box-shadow:0 20px 40px -22px rgba(16,34,43,.45); background:#000; }
        .check-pop { animation:checkPop .5s cubic-bezier(.2,.8,.2,1) both; }
        @keyframes checkPop { 0% { transform:scale(.4); opacity:0; } 60% { transform:scale(1.15); opacity:1; } 100% { transform:scale(1); } }
        .lesson-fade { animation:lessonFade .35s ease both; }
        @keyframes lessonFade { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }
        @media (prefers-reduced-motion: reduce) {
            .course-card, .course-card img, .accordion .chev, .accordion .acc-body, .enrol-card { transition:none; }
            .course-card:hover { transform:none; }
            .course-card:hover img { transform:none; }
            .check-pop, .lesson-fade { animation:none; }
        }
    
    

        /* Phase 15: homepage */
        .hero-photo { position:absolute; inset:0; overflow:hidden; }
        .hero-photo img { width:100%; height:100%; object-fit:cover; animation:kenburns 22s ease-in-out infinite alternate; }
        .hero-photo::after { content:''; position:absolute; inset:0; background:linear-gradient(180deg, rgba(16,34,43,.55), rgba(16,34,43,.75)); }
        @keyframes kenburns { from { transform:scale(1); } to { transform:scale(1.12); } }
        .hero-search { display:flex; gap:.5rem; background:#fff; border-radius:.75rem; padding:.4rem; box-shadow:0 20px 40px -20px rgba(0,0,0,.4); }
        .hero-search input { border:none; outline:none; flex:1; padding:.6rem .8rem; font-size:.95rem; background:transparent; }
        .biz-panel { background:linear-gradient(135deg, var(--teal) 0%, var(--teal-dark) 100%); color:#fff; border-radius:1.25rem; }
        .biz-feature { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.18); border-radius:1rem; }
        .count-up { font-variant-numeric:tabular-nums; }
        .cat-tile { position:relative; aspect-ratio:1/1; border-radius:1rem; overflow:hidden; display:flex; align-items:flex-end;
                    background:linear-gradient(135deg, var(--teal), var(--teal-dark)); transition:transform .3s ease, box-shadow .3s ease; }
        .cat-tile:hover { transform:translateY(-4px) scale(1.02); box-shadow:0 16px 32px -18px rgba(11,110,110,.45); }
        .cat-tile .cat-label { position:relative; z-index:1; padding:1rem; color:#fff; font-weight:600; }
        .cat-tile::before { content:''; position:absolute; inset:0; background:rgba(16,34,43,.15); }
        .carousel-track { display:flex; gap:1.25rem; overflow-x:auto; scroll-snap-type:x mandatory; scroll-behavior:smooth;
                           padding-bottom:.5rem; -ms-overflow-style:none; scrollbar-width:none; }
        .carousel-track::-webkit-scrollbar { display:none; }
        .carousel-track > * { scroll-snap-align:start; flex:0 0 auto; width:280px; }
        .carousel-btn { width:2.75rem; height:2.75rem; border-radius:50%; border:1px solid var(--line); background:#fff; display:grid; place-items:center;
                        transition:background .15s, border-color .15s; flex-shrink:0; }
        .carousel-btn:hover { border-color:var(--teal); background:var(--mint); }
        .marquee-wrap { overflow:hidden; position:relative; -webkit-mask-image:linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent);
                        mask-image:linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent); }
        .marquee-track { display:flex; gap:2.5rem; width:max-content; animation:marquee 28s linear infinite; }
        .marquee-wrap:hover .marquee-track { animation-play-state:paused; }
        @keyframes marquee { from { transform:translateX(0); } to { transform:translateX(-50%); } }
        .logo-box { display:flex; align-items:center; justify-content:center; height:3rem; padding:0 1.5rem; border-radius:.5rem;
                    background:#fff; border:1px solid var(--line); color:var(--muted); font-weight:700; letter-spacing:.02em; white-space:nowrap; }
        .instructor-card { text-align:center; }
        .instructor-photo { width:5.5rem; height:5.5rem; border-radius:50%; margin:0 auto; object-fit:cover; background:var(--mint); }
        .testimonial-slide { display:none; }
        .testimonial-slide.is-active { display:block; }
        .dot-btn { width:.55rem; height:.55rem; border-radius:50%; background:var(--line); transition:background .2s, transform .2s; }
        .dot-btn.active { background:var(--teal); transform:scale(1.3); }
        @media (prefers-reduced-motion: reduce) {
            .hero-photo img { animation:none; }
            .marquee-track { animation:none; }
            .cat-tile:hover { transform:none; }
        }
    </style>
