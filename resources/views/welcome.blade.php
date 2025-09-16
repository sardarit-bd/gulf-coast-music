<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>No Data Found — Danger Page</title>
    <style>
        :root {
            --bg1: #0b0b0c;
            --bg2: #120000;
            --accent: #ff3b3b;
            --accent-dark: #b72121;
            --stripe: #ffd24d;
            --text: #fff;
            --glass: rgba(255, 255, 255, 0.04);
        }

        * {
            box-sizing: border-box
        }

        html,
        body {
            height: 100%
        }

        body {
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
            background: radial-gradient(circle at 10% 10%, rgba(255, 59, 59, 0.06), transparent 10%), linear-gradient(180deg, var(--bg1), var(--bg2));
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Hazard stripes frame */
        .frame {
            position: relative;
            width: min(980px, 94vw);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.7), inset 0 1px 0 rgba(255, 255, 255, 0.02);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.02), rgba(0, 0, 0, 0.2));
            border: 1px solid rgba(255, 255, 255, 0.03);
        }

        .stripes {
            position: absolute;
            inset: 0;
            background-image: repeating-linear-gradient(-45deg, rgba(0, 0, 0, 0.0) 0 24px, rgba(0, 0, 0, 0.12) 24px 48px), repeating-linear-gradient(45deg, var(--stripe) 0 12px, rgba(0, 0, 0, 0) 12px 24px);
            mix-blend-mode: overlay;
            opacity: 0.15;
            pointer-events: none;
        }

        .content {
            position: relative;
            display: flex;
            gap: 32px;
            align-items: center;
            padding: 48px;
        }

        .left {
            width: 220px;
            min-width: 160px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .danger-bubble {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 25%, #ff8a8a, var(--accent-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 40px rgba(183, 33, 33, 0.35), 0 4px 12px rgba(0, 0, 0, 0.6);
            transform: rotate(-8deg);
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0) rotate(-8deg)
            }

            50% {
                transform: translateY(-8px) rotate(-6deg)
            }

            100% {
                transform: translateY(0) rotate(-8deg)
            }
        }

        /* Skull SVG sizing */
        .danger-bubble svg {
            width: 92px;
            height: 92px;
            filter: drop-shadow(0 6px 20px rgba(0, 0, 0, 0.6));
        }

        .right {
            flex: 1;
            padding-right: 24px;
        }

        h1 {
            margin: 0 0 12px 0;
            font-size: clamp(28px, 4.6vw, 44px);
            letter-spacing: -0.02em;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .flag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            padding: 6px 10px;
            border-radius: 999px;
            background: linear-gradient(180deg, rgba(0, 0, 0, 0.18), rgba(255, 255, 255, 0.02));
            border: 1px solid rgba(255, 255, 255, 0.03);
            color: var(--stripe);
            text-transform: uppercase;
            font-weight: 700;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.45);
        }

        p.lead {
            margin: 0 0 18px 0;
            color: rgba(255, 255, 255, 0.85);
            opacity: 0.95;
            font-size: clamp(14px, 2.4vw, 16px);
        }

        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            -webkit-appearance: none;
            border: 0;
            padding: 10px 16px;
            font-weight: 700;
            border-radius: 10px;
            cursor: pointer;
            backdrop-filter: blur(6px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
        }

        .btn-primary {
            background: linear-gradient(180deg, var(--accent), var(--accent-dark));
            color: #1a0606;
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .btn-primary:active {
            transform: translateY(1px)
        }

        .btn-ghost {
            background: transparent;
            color: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        /* small helper */
        .meta {
            margin-top: 12px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
        }

        /* responsive */
        @media (max-width:720px) {
            .content {
                flex-direction: column;
                padding: 28px;
                gap: 18px
            }

            .left {
                width: 100%
            }

            .danger-bubble {
                width: 140px;
                height: 140px
            }
        }

        /* subtle flicker on heading */
        .flicker {
            display: inline-block;
            animation: flick 3.5s linear infinite
        }

        @keyframes flick {
            0% {
                opacity: 1
            }

            3% {
                opacity: .3
            }

            6% {
                opacity: 1
            }

            7% {
                opacity: .6
            }

            9% {
                opacity: 1
            }

            100% {
                opacity: 1
            }
        }
    </style>
</head>

<body>
    <div class="frame" role="region" aria-label="No Data Found">
        <div class="stripes" aria-hidden="true"></div>
        <div class="content">
            <div class="left">
                <div class="danger-bubble" aria-hidden="true">
                    <!-- skull icon -->
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path
                            d="M12 2C9.79 2 8 3.79 8 6v1.1C5.5 8.6 4 11 4 13.5V15c0 1.66 1.34 3 3 3h1v1.5C8 20.88 9.12 22 10.5 22h3c1.38 0 2.5-1.12 2.5-2.5V18h1c1.66 0 3-1.34 3-3v-1.5c0-2.5-1.5-4.9-4-6.4V6c0-2.21-1.79-4-4-4z"
                            fill="#1a0606" opacity="0.05" />
                        <path
                            d="M12 3.5c1.38 0 2.5 1.12 2.5 2.5V7c1.85.87 3 2.8 3 4.5 0 1.84-.99 3.37-2.5 4.2V17c0 .83-.67 1.5-1.5 1.5H11c-.83 0-1.5-.67-1.5-1.5v-1.8C7.99 14.87 7 13.34 7 11.5 7 9.8 8.15 7.87 10 7V6c0-1.38 1.12-2.5 2.5-2.5z"
                            fill="#fff" />
                        <circle cx="9.5" cy="11.7" r="1.2" fill="#1a0606" />
                        <circle cx="14.5" cy="11.7" r="1.2" fill="#1a0606" />
                        <path d="M12 16.8c-1.1 0-2 .9-2 2v.7h4v-.7c0-1.1-.9-2-2-2z" fill="#1a0606" />
                    </svg>
                </div>
            </div>

            <div class="right">
                <div class="flag">Danger</div>
                <h1><span class="flicker">No Data Found</span></h1>
                <p class="lead">We couldn't find any records that match your request. This area is marked as dangerous
                    — proceed with care or try a different search.</p>
                <div class="meta">Tip: check filters or adjust your search query. If you think this is an error,
                    contact support.</div>
            </div>

        </div>
    </div>
</body>

</html>
