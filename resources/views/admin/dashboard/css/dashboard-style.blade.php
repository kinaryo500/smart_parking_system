@push('styles')
    <style>
        .filter-section {
            background: #ffffff;
            padding: 18px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            margin-bottom: 25px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .qr-mini-card {
            background: #ffffff;
            padding: 12px;
            border-radius: 16px;
            text-align: center;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            width: 120px;
        }

        #qr-container svg {
            width: 90px !important;
            height: 90px !important;
            display: block;
            margin: 0 auto;
        }

        .qr-label {
            font-size: 9px;
            font-weight: 800;
            color: #64748b;
            margin-bottom: 6px;
            text-transform: uppercase;
        }


        .kantung {
            background: #ffffff;
            border-radius: 10px;
            padding: 10px;
            border: 1px solid #e2e8f0;
        }

        .kantung-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .slot-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(115px, 1fr));
            gap: 10px;
        }

        .slot {
            border-radius: 14px;
            height: 100px;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 2px solid transparent;
        }

        .slot:hover {
            transform: translateY(-3px);
        }

        .kosong {
            background: #f1f5f9;
            color: #94a3b8;
            border: 1.5px dashed #cbd5e1;
        }

        .terisi {
            background: #fffbeb;
            color: #b45309;
            border: 2px solid #fbbf24;
        }

        .kode-slot {
            position: absolute;
            top: 8px;
            left: 10px;
            font-size: 9px;
            font-weight: 800;
            opacity: 0.7;
        }

        .badge-vehicle {
            position: absolute;
            top: 8px;
            right: 8px;
            font-size: 8px;
            padding: 2px 6px;
            border-radius: 5px;
            background: rgba(0, 0, 0, 0.06);
            font-weight: 800;
        }

        .plat {
            font-size: 11px;
            font-weight: 800;
            margin-top: 6px;
            background: #1e293b;
            color: white;
            padding: 3px 8px;
            border-radius: 5px;
        }

        .status-text {
            font-size: 10px;
            font-weight: 700;
            margin-top: 4px;
            opacity: 0.6;
            letter-spacing: 0.5px;
        }

        /* Material Icon Sizing */
        .material-icons {
            font-size: 34px;
        }


        .table-bordered> :not(caption)>*>* {
            border-width: 0 1px;
            border-color: #f0f0f0;
        }

        .table-bordered {
            border: none;
        }

        .table th {
            vertical-align: middle;
            letter-spacing: 0.03em;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .qr-mini-card {
                width: 100%;
            }
        }
    </style>
@endpush