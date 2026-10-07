<footer class="site-footer">
    <div class="site-footer-main">
        <div class="site-footer-col site-footer-brand">
            <p class="site-footer-logo">{{ config('app.name') }}</p>
            <p class="site-footer-tagline">Practical, certificate-backed training for individuals and teams.</p>
        </div>

        <div class="site-footer-col">
            <p class="site-footer-heading">Learn</p>
            <a href="{{ route('courses.index') }}">Browse courses</a>
            <a href="{{ route('register') }}">Create an account</a>
            <a href="{{ route('login') }}">Log in</a>
        </div>

        <div class="site-footer-col">
            <p class="site-footer-heading">Business</p>
            <a href="{{ route('home') }}#for-business">Team training</a>
            <a href="mailto:{{ config('mail.from.address') }}?subject=Team training enquiry">Talk to sales</a>
            <a href="mailto:{{ config('mail.from.address') }}?subject=Instructor application">Become an instructor</a>
        </div>

        <div class="site-footer-col">
            <p class="site-footer-heading">Legal</p>
            <a href="{{ route('legal.terms') }}">Terms</a>
            <a href="{{ route('legal.privacy') }}">Privacy</a>
            <a href="{{ route('legal.refund-policy') }}">Refunds</a>
        </div>
    </div>

    <div class="site-footer-bottom">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>

    <style>
        .site-footer {
            margin-top: 4rem;
            background: var(--teal-dark);
            color: rgba(255, 255, 255, 0.82);
        }
        .site-footer-main {
            max-width: 72rem;
            margin: 0 auto;
            padding: 3.5rem 1rem 2.5rem;
            display: grid;
            gap: 2.5rem;
            grid-template-columns: 1.4fr 1fr 1fr 1fr;
        }
        .site-footer-col {
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
        }
        .site-footer-col a {
            color: rgba(255, 255, 255, 0.78);
            font-size: 0.9rem;
            text-decoration: none;
            width: fit-content;
        }
        .site-footer-col a:hover {
            color: #fff;
            text-decoration: underline;
        }
        .site-footer-logo {
            font-family: 'Bricolage Grotesque', sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
        }
        .site-footer-tagline {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.65);
            max-width: 22rem;
            line-height: 1.5;
        }
        .site-footer-heading {
            font-size: 0.9rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 0.15rem;
        }
        .site-footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.12);
            padding: 1.25rem 1rem;
        }
        .site-footer-bottom p {
            max-width: 72rem;
            margin: 0 auto;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.55);
        }
        @media (max-width: 768px) {
            .site-footer-main {
                grid-template-columns: 1fr 1fr;
            }
            .site-footer-brand {
                grid-column: 1 / -1;
            }
        }
        @media (max-width: 480px) {
            .site-footer-main {
                grid-template-columns: 1fr;
            }
        }
    </style>
</footer>
