<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 0; }

    @foreach($fonts as $font)
        @continue(!$font || !$font->absolutePath())
        @font-face {
            font-family: '{{ $font->cssFamily() }}';
            src: url('{{ $font->absolutePath() }}') format('{{ $font->fontFormat() }}');
        }
    @endforeach

    body {
        font-family: sans-serif;
        margin: 0;
        padding: 0;
        width: 842px;
        height: 595px;
        position: relative;
        @if(!$backgroundPath)
            border: 10px solid #0B6E6E;
        @endif
    }
    .bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 842px;
        height: 595px;
    }
    .badge {
        position: absolute;
        top: 24px;
        right: 24px;
        width: 110px;
        height: 110px;
    }
    .content {
        position: relative;
        text-align: center;
        padding: 80px 70px;
    }
    .eyebrow {
        color: #5B6B75;
        letter-spacing: 2px;
        font-size: 12px;
        font-family: '{{ $fonts['eyebrow']?->cssFamily() ?? 'sans-serif' }}';
    }
    h1 {
        font-size: 20px;
        color: #10222B;
        margin: 30px 0 6px;
        font-family: '{{ $fonts['heading']?->cssFamily() ?? 'sans-serif' }}';
    }
    .name {
        font-size: 34px;
        font-weight: bold;
        color: #0B6E6E;
        margin: 20px 0;
        font-family: '{{ $fonts['name']?->cssFamily() ?? 'sans-serif' }}';
    }
    .course {
        font-size: 22px;
        margin: 10px 0 30px;
        font-family: '{{ $fonts['course']?->cssFamily() ?? 'sans-serif' }}';
    }
    .meta {
        font-size: 12px;
        color: #5B6B75;
        margin-top: 40px;
        font-family: '{{ $fonts['meta']?->cssFamily() ?? 'sans-serif' }}';
    }
</style>
</head>
<body>
    @if($backgroundPath)
        <img src="{{ $backgroundPath }}" class="bg">
    @endif

    @if($badgePath)
        <img src="{{ $badgePath }}" class="badge">
    @endif

    <div class="content">
        <p class="eyebrow">CERTIFICATE OF COMPLETION</p>
        <h1>This certifies that</h1>
        <p class="name">{{ $studentName }}</p>
        <h1>has successfully completed</h1>
        <p class="course">{{ $courseTitle }}</p>
        <p class="meta">Completed {{ $completedAt->format('j F Y') }} &middot; Verification code: {{ $code }}</p>
    </div>
</body>
</html>
