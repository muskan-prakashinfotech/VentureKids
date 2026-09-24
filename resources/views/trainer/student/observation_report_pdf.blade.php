<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Teacher Observation Report</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; background: #fff; }
    </style>
</head>
<body>

@php
    $nameParts   = explode(' ', trim($student->name), 2);
    $nameFirst   = strtoupper($nameParts[0] ?? '');
    $nameLast    = strtoupper($nameParts[1] ?? '');
    $badgeColors = ['#f0b429', '#8b5cf6', '#f97316'];
@endphp

<table style="width:100%; border-collapse:collapse;">
    <thead>
        <tr>
            <td style="padding:0;">
                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td style="background:#ffffff; padding:12px 22px; width:42%; vertical-align:middle;">
                            @if($logoData)
                                <img src="{{ $logoData }}" alt="" style="height:46px;">
                            @endif
                        </td>
                        <td style="background:#1c1c2e; padding:12px 22px; vertical-align:middle; text-align:right;">
                            <span style="color:#fff; font-size:15px; font-weight:bold; letter-spacing:2px;">TEACHER OBSERVATION REPORT</span>
                        </td>
                    </tr>
                </table>

                <div style="background:#fff; padding:14px 22px 0 22px;">
                    <table style="width:100%; border-collapse:collapse; margin-bottom:10px;">
                        <tr>
                            <td style="width:82px; vertical-align:middle; padding-right:14px;">
                                @if($studentPhotoData)
                                    <img src="{{ $studentPhotoData }}" alt=""
                                         style="width:68px; height:68px; border-radius:50%; border:3px solid #4F46E5;">
                                @else
                                    <div style="width:68px; height:68px; border-radius:50%; background:#f5e6c8; border:3px solid #4F46E5;"></div>
                                @endif
                            </td>
                            <td style="vertical-align:middle; padding-right:18px;">
                                <div style="font-size:21px; font-weight:bold; color:#1c1c2e; line-height:1.1;">{{ $nameFirst }}</div>
                                @if($nameLast)
                                <div style="font-size:13px; font-weight:bold; color:#666; line-height:1.2;">{{ $nameLast }}</div>
                                @endif
                            </td>
                            <td style="width:2px; background:#ddd; padding:0;">&nbsp;</td>
                            <td style="vertical-align:middle; padding:0 16px; text-align:center;">
                                <div style="font-size:8px; color:#999; font-weight:bold; letter-spacing:.5px; margin-bottom:3px;">Programme Level</div>
                                <div style="font-size:11px; font-weight:bold; color:#333;">{{ $gradeName ?: '—' }}</div>
                            </td>
                            <td style="width:2px; background:#ddd; padding:0;">&nbsp;</td>
                            <td style="vertical-align:middle; padding:0 16px; text-align:center;">
                                <div style="font-size:8px; color:#999; font-weight:bold; letter-spacing:.5px; margin-bottom:3px;">Duration</div>
                                <div style="font-size:11px; font-weight:bold; color:#333;">
                                    @if($durationFrom || $durationTo)
                                        {{ $durationFrom }}{{ ($durationFrom && $durationTo) ? ' – ' : '' }}{{ $durationTo }}
                                    @else
                                        All Time
                                    @endif
                                </div>
                            </td>
                            <td style="width:2px; background:#ddd; padding:0;">&nbsp;</td>
                            <td style="vertical-align:middle; padding:0 0 0 16px; text-align:center;">
                                <div style="font-size:8px; color:#999; font-weight:bold; letter-spacing:.5px; margin-bottom:3px;">Teacher</div>
                                <div style="font-size:11px; font-weight:bold; color:#333;">{{ $trainerName ?: '—' }}</div>
                            </td>
                        </tr>
                    </table>

                    <div style="border-top:2px dotted #ddd; margin-bottom:12px;"></div>
                </div>
            </td>
        </tr>
    </thead>
    <tbody>

    @if($obsData->isEmpty())
        <tr>
            <td style="padding:0 22px;">
                <div style="text-align:center; color:#aaa; font-size:14px; padding:60px 0;">
                    No observations found for the selected period.
                </div>
            </td>
        </tr>
    @else

    {{-- ══ SECTION 1 — MY JOURNEY ══ --}}
    <tr>
        <td style="padding:0 22px 16px 22px;">
            <div style="background:#ffffff; border:1.5px solid #e0e0e0; border-radius:14px; padding:14px 16px 16px 16px;">
                <table style="border-collapse:collapse; margin-bottom:12px;">
                    <tr>
                        <td style="width:42px; padding-right:0; vertical-align:middle;">
                            <div style="width:38px; height:38px; background:#1c1c2e; border-radius:50%;
                                        text-align:center; padding-top:10px;
                                        color:#fff; font-size:16px; font-weight:bold; line-height:1;">1</div>
                        </td>
                        <td style="padding:0; vertical-align:middle;">
                            <div style="background:#4F46E5; border-radius:0 8px 8px 0;
                                        padding:9px 22px; color:#fff;
                                        font-size:13px; font-weight:bold; letter-spacing:1px;">MY JOURNEY</div>
                        </td>
                    </tr>
                </table>
                @if($photos->isNotEmpty())
                <table style="width:100%; border-collapse:collapse; margin-bottom:12px;">
                    <tr>
                        @foreach($photos as $photo)
                        <td style="width:25%; padding:0 4px; vertical-align:top;">
                            <img src="{{ $photo }}" alt=""
                                 style="width:168px; height:115px; display:block; border-radius:6px;">
                        </td>
                        @endforeach
                    </tr>
                </table>
                @endif
                @if($aiSummary)
                <div style="border:1px solid #e0e0e0; border-radius:8px; padding:14px 16px;">
                    <div style="font-size:11px; color:#333; line-height:1.7;">{{ $aiSummary }}</div>
                </div>
                @endif
            </div>
        </td>
    </tr>

    @if($skillsDemonstrated->isNotEmpty())
    @foreach($skillsDemonstrated->chunk(3) as $chunkIdx => $rowChunk)
    <tr>
        <td style="padding:0 22px 10px 22px;">
            <div style="background:#eef4fc; border:1.5px solid #93b4de; border-radius:14px;
                        padding:14px 16px;">
                @if($chunkIdx === 0)
                <table style="border-collapse:collapse; margin-bottom:12px;">
                    <tr>
                        <td style="width:42px; padding-right:0; vertical-align:middle;">
                            <div style="width:38px; height:38px; background:#1c1c2e; border-radius:50%;
                                        text-align:center; padding-top:10px;
                                        color:#fff; font-size:16px; font-weight:bold; line-height:1;">2</div>
                        </td>
                        <td style="padding:0; vertical-align:middle;">
                            <div style="background:#2563a8; border-radius:0 8px 8px 0;
                                        padding:9px 22px; color:#fff;
                                        font-size:13px; font-weight:bold; letter-spacing:1px;">SKILLS DEMONSTRATED</div>
                        </td>
                    </tr>
                </table>
                @endif
                <table style="width:100%; border-collapse:separate; border-spacing:10px 0;">
                    <tr>
                        @foreach($rowChunk as $skill)
                        {{-- Count badge sits in its own row at the bottom-right of the card, in
                             normal flow — not position:absolute, which clipped the number. --}}
                        <td style="width:33%; vertical-align:top;">
                            <table style="width:100%; border-collapse:collapse; background:#fff;
                                          border:1px solid #c5d8f0; border-radius:10px;">
                                <tr>
                                    <td style="padding:16px 10px 6px 10px; text-align:center;">
                                        @if(!empty($skill['icon_data']))
                                        <img src="{{ $skill['icon_data'] }}" alt=""
                                             style="width:52px; height:52px; display:block; margin:0 auto 8px;">
                                        @endif
                                        <div style="font-size:10px; font-weight:bold; color:#222;
                                                    text-transform:uppercase; letter-spacing:.5px; margin-bottom:10px;">{{ $skill['name'] }}</div>
                                        <table style="border-collapse:collapse; margin:0 auto;">
                                            <tr>
                                                <td style="background:#27ae60; border-radius:20px;
                                                           padding:4px 14px; color:#fff; font-size:9px; font-weight:bold;">
                                                    &#10003; Demonstrated
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align:right; padding:4px 8px 12px 0;">
                                        <div style="display:inline-block; width:22px; height:22px; background:#e07b2a;
                                                    border-radius:50%; color:#fff; font-size:11px; font-weight:bold;
                                                    text-align:center; line-height:22px; vertical-align:middle;">{{ $skill['count'] }}</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        @endforeach
                        @for($i = $rowChunk->count(); $i < 3; $i++)
                        <td style="width:33%;"></td>
                        @endfor
                    </tr>
                </table>
            </div>
        </td>
    </tr>
    @endforeach
    @endif

    @if($mindsetsDemonstrated->isNotEmpty())
    @php
        $mindsetChunks = $mindsetsDemonstrated->chunk(3)->values();
        $mindsetChunkCount = $mindsetChunks->count();
    @endphp
    @foreach($mindsetChunks as $chunkIdx => $rowChunk)
    @php
        $isFirstChunk = $chunkIdx === 0;
        $isLastChunk  = $chunkIdx === $mindsetChunkCount - 1;
        $chunkBorder  = 'border-left:1.5px solid #7dd5a0; border-right:1.5px solid #7dd5a0;'
                       . ($isFirstChunk ? 'border-top:1.5px solid #7dd5a0;' : '')
                       . ($isLastChunk ? 'border-bottom:1.5px solid #7dd5a0;' : '');
        $chunkRadius  = ($isFirstChunk ? 'border-top-left-radius:14px; border-top-right-radius:14px;' : '')
                       . ($isLastChunk ? 'border-bottom-left-radius:14px; border-bottom-right-radius:14px;' : '');
    @endphp
    <tr>
        <td style="padding:0 22px {{ $isLastChunk ? '10px' : '0' }} 22px;">
            <div style="background:#edf9f2; {{ $chunkBorder }} {{ $chunkRadius }}
                        padding:14px 16px {{ $isLastChunk ? '18px' : '4px' }} 16px;">
                @if($isFirstChunk)
                <table style="border-collapse:collapse; margin-bottom:14px;">
                    <tr>
                        <td style="width:42px; padding-right:0; vertical-align:middle;">
                            <div style="width:38px; height:38px; background:#1c1c2e; border-radius:50%;
                                        text-align:center; padding-top:10px;
                                        color:#fff; font-size:16px; font-weight:bold; line-height:1;">3</div>
                        </td>
                        <td style="padding:0; vertical-align:middle;">
                            <div style="background:#16a85f; border-radius:0 8px 8px 0;
                                        padding:9px 22px; color:#fff;
                                        font-size:13px; font-weight:bold; letter-spacing:1px;">MINDSETS BUILT</div>
                        </td>
                    </tr>
                </table>
                @endif
                <table style="width:100%; border-collapse:separate; border-spacing:10px 0;">
                    <tr>
                        @foreach($rowChunk as $idx => $mindset)
                        @php
                            $color     = $badgeColors[$idx % count($badgeColors)];
                            $textColor = ($color === '#f0b429') ? '#6b3e00' : '#ffffff';
                        @endphp
                        <td style="width:33%; text-align:center; vertical-align:top;">
                            <div style="width:66px; height:66px; background:{{ $color }}; border-radius:50%;
                                        margin:0 auto; padding:14px; text-align:center;">
                                @if(!empty($mindset['icon_data']))
                                <img src="{{ $mindset['icon_data'] }}" alt=""
                                     style="width:38px; height:38px; display:block; margin:0 auto;">
                                @endif
                            </div>
                            {{-- Count badge sits inside the name pill itself — a small
                                 <span> circle (not a <td>) next to the text, so it reads as
                                 one "EMPATHY [3]" unit instead of a separate floating badge. --}}
                            <table style="border-collapse:collapse; margin:8px auto 0;">
                                <tr>
                                    <td style="background:{{ $color }}; border-radius:6px;
                                               padding:4px 8px; text-align:center; white-space:nowrap;">
                                        <span style="font-size:9px; font-weight:bold; color:{{ $textColor }};
                                                     text-transform:uppercase; letter-spacing:.5px;
                                                     line-height:1.35; vertical-align:middle;">{{ $mindset['name'] }}</span>
                                        <span style="display:inline-block; width:16px; height:16px; background:#e07b2a;
                                                     border-radius:50%; color:#fff; font-size:9px; font-weight:bold;
                                                     text-align:center; line-height:16px; margin-left:5px; vertical-align:middle;">{{ $mindset['count'] }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        @endforeach
                        @for($i = $rowChunk->count(); $i < 3; $i++)
                        <td style="width:33%;"></td>
                        @endfor
                    </tr>
                </table>
            </div>
        </td>
    </tr>
    @endforeach
    @endif

    {{-- ══ SECTION 4 — MOMENTS THAT MADE ME SHINE (every skill/mindset, with all its
         anecdotal notes for the selected range, numbered per trait) ══ --}}
    @if($momentGroups->isNotEmpty())
    @foreach($momentGroups as $groupIdx => $group)
    <tr>
        <td style="padding:0 22px 10px 22px;">
            <div style="background:#fff8ee; border:1.5px dashed #f9c97c; border-radius:10px;
                        padding:12px 16px;">
                @if($groupIdx === 0)
                <table style="border-collapse:collapse; margin-bottom:12px;">
                    <tr>
                        <td style="width:42px; padding-right:0; vertical-align:middle;">
                            <div style="width:38px; height:38px; background:#1c1c2e; border-radius:50%;
                                        text-align:center; padding-top:10px;
                                        color:#fff; font-size:16px; font-weight:bold; line-height:1;">4</div>
                        </td>
                        <td style="padding:0; vertical-align:middle;">
                            <div style="background:#4F46E5; border-radius:0 8px 8px 0;
                                        padding:9px 22px; color:#fff;
                                        font-size:13px; font-weight:bold; letter-spacing:1px;">MOMENTS THAT MADE ME SHINE</div>
                        </td>
                    </tr>
                </table>
                @endif
                <div style="font-size:11px; font-weight:bold; color:#222;
                            text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px;">{{ $group['name'] }}</div>
                <ol style="margin:0; padding-left:16px;">
                    @foreach($group['notes'] as $note)
                    <li style="font-size:10px; color:#444; line-height:1.7; margin-bottom:4px;">{{ $note }}</li>
                    @endforeach
                </ol>
            </div>
        </td>
    </tr>
    @endforeach
    @endif

    @endif {{-- /obsData not empty --}}

    </tbody>
</table>
</body>
</html>
