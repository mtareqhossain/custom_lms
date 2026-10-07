<x-lms-layout title="Terms of Service">
<div class="mx-auto max-w-3xl px-4 py-12 prose-lms">
    <p class="pill">Draft — read before launch</p>
    <h1 class="display mt-4 text-3xl">Terms of Service</h1>
    <p class="muted">Last updated: [FILL IN DATE]</p>

    <h2>1. Who we are</h2>
    <p>[FILL IN: legal company name, registered address, company number if applicable] ("we", "us")
       operates {{ config('app.name') }}.</p>

    <h2>2. Accounts</h2>
    <p>You are responsible for keeping your login credentials secure. Business accounts are
       responsible for the accounts of students they enroll via bulk import.</p>

    <h2>3. Courses and access</h2>
    <p>Free courses grant lifetime access unless stated otherwise. Paid courses grant access for
       the period stated at purchase ([FILL IN: typical access length, e.g. "90 days" or
       "lifetime"]), after which access ends unless renewed.</p>

    <h2>4. Payments</h2>
    <p>Payments are processed by Stripe. We do not store your card details. See our
       <a href="{{ route('legal.refund-policy') }}">Refund Policy</a> for cancellations and refunds.</p>

    <h2>5. Certificates</h2>
    <p>Certificates are issued on completion of all lessons in a course and reflect completion of
       our content only; they are not an accredited qualification unless explicitly stated
       [FILL IN if applicable].</p>

    <h2>6. Acceptable use</h2>
    <p>[FILL IN: prohibited conduct — sharing accounts, redistributing course content, abuse of
       the platform.]</p>

    <h2>7. Liability</h2>
    <p>[FILL IN: standard liability limitation clause — recommend legal review for your
       jurisdiction.]</p>

    <h2>8. Contact</h2>
    <p>Questions about these terms: [FILL IN contact email].</p>
</div>
</x-lms-layout>
