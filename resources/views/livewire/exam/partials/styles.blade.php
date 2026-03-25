@push('styles')
    <style>
        .violation-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .violation-content {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            max-width: 450px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-top: 6px solid #ef4444;
        }

        .violation-icon {
            width: 80px;
            height: 80px;
            background: #fee2e2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: #ef4444;
        }


        /* ================= LAYOUT ================= */
        .container {
            display: grid;
            grid-template-columns: 1fr 280px;
            gap: 20px;
            padding: 20px;
            padding-top: 10px;
            max-width: 1400px;
            margin: 0 auto;
            min-height: calc(100vh - 140px);
            align-items: start;
            /* Prevents question box from stretching to match sidebar height */
        }

        /* ================= SECTION SOAL ================= */
        .question-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .question-number {
            font-weight: 700;
            font-size: 16px;
            color: #333;
            margin-bottom: 8px;
        }

        .question-content {
            margin-top: 12px;
        }

        .question-text {
            font-size: 16px;
            margin-bottom: 16px;
            line-height: 1.6;
            color: #333;
        }

        /* ================= OPSI JAWABAN ================= */
        .options {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
        }

        .option {
            border: 1px solid #dcdcdc;
            padding: 10px 12px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
            background: #ffffff;
            min-height: auto;
        }

        .option:hover {
            background: #f5f5f5;
            border-color: #999;
        }

        .option.selected {
            background: #e8f5e9;
            border-color: #2e7d32;
        }

        .option input[type="radio"] {
            width: 18px;
            height: 18px;
            margin: 0;
            cursor: pointer;
            flex-shrink: 0;
            accent-color: #2e7d32;
        }

        .option-text {
            flex: 1;
            font-size: 14px;
            line-height: 1.4;
            margin: 0;
            color: #333;
        }

        .option.selected .option-text {
            color: #1b5e20;
            font-weight: 500;
        }

        /* ================= RAGU-RAGU ================= */
        .flag-toggle {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid #dcdcdc;
            background: white;
            cursor: pointer;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
            margin-top: 18px;
        }

        .flag-toggle svg {
            width: 18px;
            height: 18px;
        }

        .flag-toggle:hover {
            background: #fff3cd;
            border-color: #f9a825;
        }

        .flag-toggle.active {
            background: #f9a825;
            color: #000;
            border-color: #f9a825;
        }

        /* ================= NAVIGASI ================= */
        .navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            gap: 12px;
        }

        button {
            padding: 12px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        button.primary {
            background: #2e7d32;
            color: white;
        }

        button.primary:hover:not(:disabled) {
            background: #1b5e20;
        }

        button.secondary {
            background: #e0e0e0;
            color: #333;
        }

        button.secondary:hover:not(:disabled) {
            background: #bdbdbd;
        }

        /* ================= SIDEBAR ================= */
        .sidebar {
            background: white;
            border-radius: 12px;
            padding: 20px;

            position: sticky;
            top: 110px;
            /* Adjusted for Fixed Header 100px + 10px Gap */
            align-self: start;

            /* Full height display (no internal scroll) */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #eee;
        }

        /* Scrollbar styles removed as sidebar is full height */

        .sidebar h3 {
            margin-top: 0;
            font-size: 18px;
            font-weight: 700;
            color: #333;
            margin-bottom: 16px;
        }

        /* LEGEND */
        .legend {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 18px;
            margin-bottom: 15px;
        }

        .legend-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .box {
            width: 14px;
            height: 14px;
            border-radius: 4px;
            flex-shrink: 0;
        }

        .text {
            font-size: 14px;
            white-space: nowrap;
        }

        .status-belum {
            background: #bdbdbd;
        }

        .status-ragu {
            background: #f9a825;
        }

        .status-jawab {
            background: #2e7d32;
        }

        .status-aktif {
            background: #1976d2;
        }

        /* ================= DAFTAR SOAL ================= */
        .question-list {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
            margin-bottom: 20px;
        }

        .question-list button,
        .nav-indicator {
            padding: 10px;
            background: #e0e0e0;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 14px;
            color: #333;
        }

        .question-list button:hover,
        .nav-indicator:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .question-list button.answered,
        .nav-indicator.answered {
            background: #2e7d32;
            color: white;
        }

        .question-list button.doubt,
        .nav-indicator.doubtful {
            background: #f9a825;
            color: #000;
        }

        .question-list button.current,
        .nav-indicator.current {
            background: #1976d2;
            color: white;
            box-shadow: 0 4px 12px rgba(25, 118, 210, 0.4);
        }

        /* ================= FINISH BUTTON ================= */
        .finish {
            width: 100%;
            background: #c62828;
            color: white;
            padding: 12px;
            border-radius: 8px;
            border: none;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .finish:hover {
            background: #b71c1c;
            box-shadow: 0 4px 12px rgba(198, 40, 40, 0.4);
        }

        /* ================= ANSWER STATS ================= */
        .answer-stats {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #e0e0e0;
        }

        .answer-stats>div {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
        }

        /* ================= SAVE INDICATOR ================= */
        .save-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 6px;
            background: #d4edda;
            color: #155724;
            font-size: 13px;
            font-weight: 600;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .save-indicator.show {
            opacity: 1;
        }

        .save-indicator svg {
            width: 16px;
            height: 16px;
        }

        /* ================= RESPONSIVE ================= */
        @media (max-width: 1024px) {
            .container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
                margin-top: 0;
                top: auto;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 12px;
                gap: 12px;
            }

            .question-section {
                padding: 16px;
            }

            .question-list {
                grid-template-columns: repeat(4, 1fr) !important;
            }
        }

        @media (max-width: 640px) {
            .question-list {
                grid-template-columns: repeat(3, 1fr) !important;
            }

            .navigation {
                flex-direction: column;
            }

            button {
                width: 100%;
            }
        }

        /* ==============whitewhite#9ca3af=== LOADING ANIMATION ================= */
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }
    </style>
@endpush
