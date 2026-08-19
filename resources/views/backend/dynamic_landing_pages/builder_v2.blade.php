<!DOCTYPE html>
<html lang="en">
@php
    $info = \App\Models\Information::first();
    $logoUrl = ($info && !empty($info->site_logo))
        ? asset('uploads/img/'.$info->site_logo)
        : asset('backend/img/default-logo.svg');
@endphp
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Page Builder V2 - {{ $info->site_name ?? 'Admin' }}</title>
    <link rel="shortcut icon" href="{{ $logoUrl }}">
    <link href="{{ asset('backend/css/icons.min.css') }}" rel="stylesheet">
    <style>
        :root {
            --v2-bg: #f7f9fb;
            --v2-surface: #ffffff;
            --v2-surface-soft: #f2f4f6;
            --v2-surface-mid: #e6e8ea;
            --v2-text: #191c1e;
            --v2-muted: #5f6673;
            --v2-line: #cfd5dd;
            --v2-line-strong: #aeb7c4;
            --v2-primary: #3525cd;
            --v2-primary-soft: #e2dfff;
            --v2-danger: #ba1a1a;
            --v2-danger-soft: #ffdad6;
            --v2-sidebar: 280px;
            --v2-panel: 500px;
            --v2-topbar: 56px;
            --v2-radius: 4px;
            --v2-radius-lg: 8px;
            --v2-shadow: 0 18px 45px rgba(19, 27, 46, .16);
        }

        * {
            box-sizing: border-box;
        }

        body {
            background: var(--v2-bg);
            color: var(--v2-text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 14px;
            line-height: 1.45;
            margin: 0;
            overflow-x: hidden;
        }

        button,
        input,
        textarea,
        select {
            font: inherit;
        }

        button {
            cursor: pointer;
        }

        .v2-topbar {
            align-items: center;
            background: var(--v2-surface);
            border-bottom: 1px solid var(--v2-line);
            display: flex;
            height: var(--v2-topbar);
            justify-content: space-between;
            left: 0;
            padding: 0 24px;
            position: fixed;
            right: 0;
            top: 0;
            z-index: 50;
        }

        .v2-brand {
            align-items: center;
            display: flex;
            gap: 10px;
            min-width: 0;
        }

        .v2-brand-mark {
            align-items: center;
            background: var(--v2-primary);
            border-radius: var(--v2-radius);
            color: #fff;
            display: inline-flex;
            height: 28px;
            justify-content: center;
            width: 28px;
        }

        .v2-brand-title {
            color: var(--v2-text);
            font-size: 20px;
            font-weight: 800;
            margin: 0;
        }

        .v2-top-actions,
        .v2-inline-actions {
            align-items: center;
            display: flex;
            gap: 8px;
        }

        .v2-history {
            align-items: center;
            background: var(--v2-surface-soft);
            border: 1px solid var(--v2-line);
            border-radius: var(--v2-radius);
            display: inline-flex;
            gap: 2px;
            height: 36px;
            margin-right: 8px;
            padding: 0 6px;
        }

        .v2-icon-btn,
        .v2-mini-btn {
            align-items: center;
            background: transparent;
            border: 0;
            border-radius: var(--v2-radius);
            color: var(--v2-muted);
            display: inline-flex;
            height: 28px;
            justify-content: center;
            width: 28px;
        }

        .v2-icon-btn:hover,
        .v2-mini-btn:hover {
            background: var(--v2-surface-mid);
            color: var(--v2-text);
        }

        .v2-btn {
            align-items: center;
            border: 1px solid var(--v2-line-strong);
            border-radius: var(--v2-radius);
            display: inline-flex;
            font-size: 12px;
            font-weight: 700;
            gap: 6px;
            height: 36px;
            justify-content: center;
            padding: 0 16px;
            text-decoration: none;
            transition: background .18s ease, color .18s ease, opacity .18s ease, transform .18s ease;
            white-space: nowrap;
        }

        .v2-btn:hover {
            transform: translateY(-1px);
        }

        .v2-btn-light {
            background: var(--v2-surface);
            color: var(--v2-text);
        }

        .v2-btn-primary {
            background: var(--v2-primary);
            border-color: var(--v2-primary);
            color: #fff;
        }

        .v2-btn-dark {
            background: var(--v2-text);
            border-color: var(--v2-text);
            color: #fff;
        }

        .v2-btn-danger {
            background: var(--v2-danger-soft);
            border-color: var(--v2-danger-soft);
            color: var(--v2-danger);
        }

        .v2-btn[disabled],
        .v2-icon-btn[disabled],
        .v2-mini-btn[disabled] {
            cursor: wait;
            opacity: .55;
            transform: none;
        }

        .v2-shell {
            display: flex;
            height: 100vh;
            overflow: hidden;
            padding-top: var(--v2-topbar);
        }

        .v2-sidebar {
            background: var(--v2-surface);
            border-right: 1px solid var(--v2-line);
            display: flex;
            flex-direction: column;
            gap: 16px;
            height: calc(100vh - var(--v2-topbar));
            left: 0;
            overflow: hidden;
            padding: 24px;
            position: fixed;
            top: var(--v2-topbar);
            width: var(--v2-sidebar);
            z-index: 40;
        }

        .v2-kicker {
            color: var(--v2-muted);
            font-size: 12px;
            font-weight: 700;
            margin: 2px 0 0;
        }

        .v2-heading {
            font-size: 16px;
            font-weight: 800;
            margin: 0;
        }

        .v2-tabs {
            border-bottom: 1px solid var(--v2-line);
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            margin: 0;
        }

        .v2-tabs[hidden],
        .v2-form-stack[hidden] {
            display: none !important;
        }

        .v2-tab {
            background: transparent;
            border: 0;
            border-left: 4px solid transparent;
            color: var(--v2-muted);
            font-size: 12px;
            font-weight: 800;
            min-height: 36px;
            padding: 8px 8px 8px 10px;
            text-align: left;
        }

        .v2-tab.is-active {
            border-left-color: var(--v2-primary);
            color: var(--v2-primary);
        }

        .v2-form-stack {
            display: grid;
            gap: 14px;
        }

        .v2-sidebar-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        .v2-field-group {
            border-top: 1px solid var(--v2-line);
            display: grid;
            gap: 12px;
            padding-top: 16px;
        }

        .v2-panel[hidden] {
            display: none !important;
        }

        .v2-field-group-title {
            color: var(--v2-text);
            font-size: 13px;
            font-weight: 900;
            margin: 0;
        }

        .v2-component-panel {
            display: grid;
            gap: 12px;
        }

        .v2-component-panel[hidden] {
            display: none !important;
        }

        .v2-property-error {
            color: var(--v2-danger);
            font-size: 12px;
            font-weight: 700;
            margin-top: 5px;
        }

        .v2-field label,
        .v2-spacing-label {
            color: var(--v2-muted);
            display: block;
            font-size: 12px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .v2-field input,
        .v2-field textarea,
        .v2-field select {
            background: var(--v2-surface);
            border: 1px solid var(--v2-line);
            border-radius: var(--v2-radius);
            color: var(--v2-text);
            outline: 0;
            padding: 10px 12px;
            width: 100%;
        }

        .v2-field input[readonly] {
            background: var(--v2-surface-soft);
            color: var(--v2-muted);
        }

        .v2-field input:focus,
        .v2-field textarea:focus {
            border-color: var(--v2-primary);
            box-shadow: 0 0 0 3px rgba(53, 37, 205, .12);
        }

        .v2-field input[type="checkbox"] {
            height: auto;
            padding: 0;
            width: auto;
        }

        .v2-slug-field {
            align-items: center;
            border: 1px solid var(--v2-line);
            border-radius: var(--v2-radius);
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            overflow: hidden;
        }

        .v2-slug-prefix {
            background: var(--v2-surface-soft);
            border-right: 1px solid var(--v2-line);
            color: var(--v2-muted);
            height: 100%;
            padding: 10px 10px;
        }

        .v2-slug-field input {
            border: 0;
        }

        .v2-theme-grid {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .v2-theme-grid .v2-field label {
            font-size: 11px;
        }

        .v2-schema-array {
            min-height: 92px;
        }

        .v2-repeater {
            display: grid;
            gap: 10px;
        }

        .v2-repeater-rows {
            display: grid;
            gap: 10px;
        }

        .v2-repeater-row {
            background: var(--v2-surface-soft);
            border: 1px solid var(--v2-line);
            border-radius: var(--v2-radius);
            display: grid;
            gap: 10px;
            padding: 10px;
        }

        .v2-repeater-row-head {
            align-items: center;
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        .v2-repeater-row-title {
            color: var(--v2-muted);
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .v2-repeater-grid {
            display: grid;
            gap: 8px;
        }

        .v2-repeater-grid label {
            margin-bottom: 0;
        }

        .v2-repeater-grid span {
            color: var(--v2-muted);
            display: block;
            font-size: 11px;
            font-weight: 800;
            margin-bottom: 5px;
            text-transform: capitalize;
        }

        .v2-product-picker {
            display: grid;
            gap: 8px;
        }

        .v2-product-search {
            background: var(--v2-surface);
            border: 1px solid var(--v2-line);
            border-radius: var(--v2-radius);
            color: var(--v2-text);
            outline: 0;
            padding: 10px 12px;
            width: 100%;
        }

        .v2-product-selected,
        .v2-product-results {
            display: grid;
            gap: 6px;
        }

        .v2-product-results {
            max-height: 220px;
            overflow-y: auto;
        }

        .v2-product-option,
        .v2-product-chip {
            align-items: center;
            background: var(--v2-surface-soft);
            border: 1px solid var(--v2-line);
            border-radius: var(--v2-radius);
            color: var(--v2-text);
            display: flex;
            gap: 8px;
            justify-content: space-between;
            padding: 8px 10px;
            text-align: left;
            width: 100%;
        }

        .v2-product-option {
            cursor: pointer;
        }

        .v2-product-option.is-selected {
            border-color: var(--v2-primary);
            color: var(--v2-primary);
        }

        .v2-product-name {
            display: grid;
            gap: 2px;
            min-width: 0;
        }

        .v2-product-name strong,
        .v2-product-name small {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .v2-product-name small {
            color: var(--v2-muted);
            font-size: 11px;
        }

        .v2-spacing-grid {
            display: grid;
            gap: 8px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .v2-spacing-cell {
            position: relative;
        }

        .v2-spacing-cell input {
            background: var(--v2-surface);
            border: 1px solid var(--v2-line);
            border-radius: var(--v2-radius);
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            height: 40px;
            text-align: center;
            width: 100%;
        }

        .v2-spacing-cell span {
            background: var(--v2-surface);
            color: var(--v2-muted);
            font-size: 10px;
            left: 50%;
            padding: 0 3px;
            position: absolute;
            top: -7px;
            transform: translateX(-50%);
        }

        .v2-sidebar-footer {
            background: var(--v2-surface);
            border-top: 1px solid var(--v2-line);
            display: grid;
            flex: 0 0 auto;
            gap: 8px;
            position: relative;
            z-index: 5;
        }

        .v2-canvas {
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
            background-size: 24px 24px;
            flex: 1;
            height: calc(100vh - var(--v2-topbar));
            margin-left: var(--v2-sidebar);
            overflow-x: hidden;
            overflow-y: auto;
            padding: 24px 28px;
        }

        .v2-canvas-inner {
            display: grid;
            gap: 16px;
            margin: 0;
            max-width: none;
            padding-bottom: 80px;
            width: 100%;
        }

        .v2-alert {
            border-radius: var(--v2-radius-lg);
            display: none;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 8px;
            padding: 12px 14px;
        }

        .v2-alert.is-visible {
            display: block;
        }

        .v2-alert-info,
        .v2-alert-success {
            background: #e8f5ec;
            color: #166534;
        }

        .v2-alert-warning {
            background: #fff6d8;
            color: #8a5a00;
        }

        .v2-alert-danger {
            background: var(--v2-danger-soft);
            color: var(--v2-danger);
        }

        .v2-empty-section,
        .v2-add-bottom {
            align-items: center;
            background: rgba(242, 244, 246, .72);
            border: 2px dashed var(--v2-line);
            border-radius: var(--v2-radius);
            color: var(--v2-muted);
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 160px;
            padding: 28px;
            position: relative;
            text-align: center;
        }

        .v2-empty-title {
            color: var(--v2-line-strong);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .v2-add-circle {
            align-items: center;
            background: var(--v2-primary);
            border: 0;
            border-radius: 999px;
            color: #fff;
            display: inline-flex;
            height: 34px;
            justify-content: center;
            margin: -2px auto;
            width: 34px;
            z-index: 1;
        }

        .v2-component {
            background: transparent;
            border: 1px solid var(--v2-line);
            border-radius: var(--v2-radius-lg);
            box-shadow: 0 1px 0 rgba(19, 27, 46, .04);
            overflow: hidden;
            position: relative;
            width: 100%;
            max-width: 100%;
        }

        .v2-component.is-active {
            outline: 2px solid var(--v2-primary);
            box-shadow: var(--v2-shadow);
        }

        .v2-component-actions {
            align-items: center;
            background: rgba(255, 255, 255, .84);
            border: 1px solid var(--v2-line);
            border-radius: var(--v2-radius-lg);
            display: flex;
            gap: 2px;
            padding: 4px;
            position: absolute;
            right: 12px;
            top: 12px;
            z-index: 3;
        }

        .v2-component-body {
            padding: 40px;
            text-align: center;
        }

        .v2-component-preview {
            background: var(--v2-surface);
            contain: layout paint;
            min-height: 180px;
            overflow: hidden;
            position: relative;
            width: 100%;
            max-width: 100%;
        }

        .v2-component-preview iframe {
            border: 0;
            display: block;
            left: 0;
            min-height: 220px;
            max-width: none;
            pointer-events: none;
            position: absolute;
            top: 0;
            transform-origin: top left;
            width: 1440px;
        }

        .v2-component-label {
            align-items: center;
            background: rgba(255, 255, 255, .9);
            border: 1px solid var(--v2-line);
            border-radius: var(--v2-radius-lg);
            color: var(--v2-muted);
            display: inline-flex;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 11px;
            font-weight: 800;
            gap: 6px;
            left: 12px;
            max-width: calc(100% - 104px);
            overflow: hidden;
            padding: 6px 8px;
            position: absolute;
            text-overflow: ellipsis;
            top: 12px;
            white-space: nowrap;
            z-index: 3;
        }

        .v2-component-label span {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .v2-component-key {
            color: var(--v2-muted);
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 12px;
            margin-bottom: 10px;
        }

        .v2-component-title {
            font-size: 28px;
            font-weight: 900;
            margin: 0 0 12px;
        }

        .v2-component-copy {
            color: var(--v2-muted);
            font-size: 16px;
            margin: 0 auto;
            max-width: 560px;
        }

        .v2-component-state {
            align-items: center;
            display: inline-flex;
            gap: 6px;
            margin-top: 18px;
        }

        .v2-add-bottom {
            background: transparent;
            color: var(--v2-line-strong);
            min-height: 96px;
        }

        .v2-picker {
            inset: 0;
            opacity: 0;
            padding: 16px;
            pointer-events: none;
            position: fixed;
            transition: opacity .22s ease;
            z-index: 60;
        }

        .v2-picker.is-open {
            opacity: 1;
            pointer-events: auto;
        }

        .v2-picker-backdrop {
            background: rgba(25, 28, 30, .2);
            inset: 0;
            position: absolute;
        }

        .v2-picker-panel {
            background: var(--v2-surface);
            border: 1px solid var(--v2-line);
            border-radius: var(--v2-radius-lg);
            box-shadow: var(--v2-shadow);
            display: flex;
            flex-direction: column;
            height: min(800px, calc(100vh - 32px));
            margin-left: auto;
            max-width: var(--v2-panel);
            overflow: hidden;
            position: relative;
            transform: translateX(48px);
            transition: transform .22s ease;
            width: 100%;
        }

        .v2-picker.is-open .v2-picker-panel {
            transform: translateX(0);
        }

        .v2-picker-head,
        .v2-picker-footer {
            border-bottom: 1px solid var(--v2-line);
            padding: 24px;
        }

        .v2-picker-head {
            align-items: flex-start;
            display: flex;
            justify-content: space-between;
            gap: 16px;
        }

        .v2-picker-body {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
        }

        .v2-picker-footer {
            background: var(--v2-surface-soft);
            border-bottom: 0;
            border-top: 1px solid var(--v2-line);
        }

        .v2-component-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .v2-catalog-card {
            background: var(--v2-surface);
            border: 1px solid var(--v2-line);
            border-radius: var(--v2-radius-lg);
            color: var(--v2-text);
            cursor: pointer;
            display: block;
            padding: 6px;
            text-align: left;
            width: 100%;
        }

        .v2-catalog-card:hover {
            border-color: var(--v2-primary);
        }

        .v2-catalog-preview {
            align-items: center;
            background: var(--v2-surface-soft);
            border-radius: var(--v2-radius);
            display: flex;
            height: 108px;
            justify-content: center;
            margin-bottom: 8px;
            overflow: hidden;
            position: relative;
        }

        .v2-catalog-preview::before {
            background: var(--v2-line);
            border-radius: 999px;
            box-shadow: 0 12px 0 var(--v2-line), 0 24px 0 var(--v2-line);
            content: "";
            height: 8px;
            opacity: .5;
            width: 72px;
        }

        .v2-catalog-preview:has(iframe)::before {
            content: none;
        }

        .v2-catalog-preview iframe {
            border: 0;
            height: 432px;
            left: 0;
            pointer-events: none;
            position: absolute;
            top: 0;
            transform: scale(.25);
            transform-origin: top left;
            width: 1200px;
        }

        .v2-catalog-title {
            display: block;
            font-size: 12px;
            font-weight: 800;
            padding: 0 8px 8px;
        }

        .v2-catalog-meta {
            color: var(--v2-muted);
            display: block;
            font-size: 11px;
            padding: 0 8px 8px;
        }

        @media (max-width: 991px) {
            .v2-topbar {
                padding: 0 12px;
            }

            .v2-brand-title {
                font-size: 16px;
            }

            .v2-history {
                display: none;
            }

            .v2-shell {
                display: block;
                height: auto;
                min-height: 100vh;
                overflow: visible;
            }

            .v2-sidebar {
                height: auto;
                position: relative;
                top: 0;
                width: 100%;
            }

            .v2-canvas {
                height: auto;
                margin-left: 0;
                min-height: 70vh;
            }
        }

        @media (max-width: 640px) {
            .v2-top-actions .v2-btn-light {
                display: none;
            }

            .v2-component-grid {
                grid-template-columns: 1fr;
            }

            .v2-component-body {
                padding: 34px 22px;
            }
        }
    </style>
</head>
<body>
    <header class="v2-topbar">
        <div class="v2-brand">
            <span class="v2-brand-mark"><i class="mdi mdi-view-dashboard-edit-outline"></i></span>
            <h1 class="v2-brand-title">Page Builder</h1>
        </div>
        <div class="v2-top-actions">
            <div class="v2-history" aria-label="History controls">
                <button class="v2-icon-btn" type="button" disabled title="Undo"><i class="mdi mdi-undo"></i></button>
                <button class="v2-icon-btn" type="button" disabled title="Redo"><i class="mdi mdi-redo"></i></button>
            </div>
            <button class="v2-btn v2-btn-light" type="button" id="v2PreviewBtn"><i class="mdi mdi-eye-outline"></i> Preview</button>
            <button class="v2-btn v2-btn-primary" type="button" id="v2PublishBtn"><i class="mdi mdi-cloud-upload-outline"></i> Publish Page</button>
        </div>
    </header>

    <div class="v2-shell">
        <aside class="v2-sidebar">
            <div>
                <h2 class="v2-heading">Page Properties</h2>
                <p class="v2-kicker" id="v2EditingLabel">Loading page...</p>
            </div>

            <nav class="v2-tabs" id="v2PagePropertyTabs" aria-label="Page property sections">
                <button class="v2-tab is-active" type="button" data-panel-tab="layout">Layout</button>
                <button class="v2-tab" type="button" data-panel-tab="style">Style</button>
                <button class="v2-tab" type="button" data-panel-tab="seo">SEO</button>
                <button class="v2-tab" type="button" data-panel-tab="settings">Settings</button>
            </nav>

            <div class="v2-sidebar-body">
                <div class="v2-form-stack" id="v2PagePropertiesPanel">
                    <div class="v2-field v2-panel" data-panels="layout settings">
                        <label for="v2PageName">Page Title</label>
                        <input id="v2PageName" type="text" placeholder="Campaign page">
                    </div>

                    <div class="v2-field v2-panel" data-panels="layout settings">
                        <label for="v2PageSlug">URL Path</label>
                        <div class="v2-slug-field">
                            <span class="v2-slug-prefix">/landing/</span>
                            <input id="v2PageSlug" type="text" placeholder="campaign-page">
                        </div>
                    </div>

                    <div class="v2-field v2-panel" data-panels="seo settings">
                        <label for="v2SeoTitle">SEO Title</label>
                        <input id="v2SeoTitle" type="text" placeholder="Enter SEO title...">
                    </div>

                    <div class="v2-field v2-panel" data-panels="seo settings">
                        <label for="v2SeoDescription">SEO Description</label>
                        <textarea id="v2SeoDescription" rows="3" placeholder="Enter meta description..."></textarea>
                    </div>

                    <div class="v2-field-group v2-panel" data-panels="settings">
                        <h3 class="v2-field-group-title">Settings</h3>
                        <div class="v2-field">
                            <label for="v2PageStatus">Status</label>
                            <input id="v2PageStatus" type="text" readonly>
                        </div>
                        <div class="v2-field">
                            <label for="v2PublicUrl">Public URL</label>
                            <input id="v2PublicUrl" type="text" readonly>
                        </div>
                    </div>

                    <div class="v2-field-group v2-panel" data-panels="style settings">
                        <h3 class="v2-field-group-title">Theme Tokens</h3>
                        <div class="v2-theme-grid">
                            <div class="v2-field">
                                <label for="v2ThemePrimary">Primary</label>
                                <input id="v2ThemePrimary" type="text" placeholder="#2563eb">
                            </div>
                            <div class="v2-field">
                                <label for="v2ThemeSecondary">Secondary</label>
                                <input id="v2ThemeSecondary" type="text" placeholder="#0ea5e9">
                            </div>
                            <div class="v2-field">
                                <label for="v2ThemeBackground">Background</label>
                                <input id="v2ThemeBackground" type="text" placeholder="#ffffff">
                            </div>
                            <div class="v2-field">
                                <label for="v2ThemeSurface">Surface</label>
                                <input id="v2ThemeSurface" type="text" placeholder="#f8fafc">
                            </div>
                            <div class="v2-field">
                                <label for="v2ThemeText">Text</label>
                                <input id="v2ThemeText" type="text" placeholder="#111827">
                            </div>
                            <div class="v2-field">
                                <label for="v2ThemeMutedText">Muted Text</label>
                                <input id="v2ThemeMutedText" type="text" placeholder="#64748b">
                            </div>
                        </div>
                    </div>

                    <div class="v2-field-group v2-panel" data-panels="style settings">
                        <h3 class="v2-field-group-title">Margin</h3>
                        <div class="v2-spacing-grid">
                            <label class="v2-spacing-cell"><input id="v2MarginTop" type="text" value="0"><span>TOP</span></label>
                            <label class="v2-spacing-cell"><input id="v2MarginRight" type="text" value="0"><span>RIGHT</span></label>
                            <label class="v2-spacing-cell"><input id="v2MarginBottom" type="text" value="0"><span>BTM</span></label>
                            <label class="v2-spacing-cell"><input id="v2MarginLeft" type="text" value="0"><span>LFT</span></label>
                        </div>
                    </div>

                    <div class="v2-field-group v2-panel" data-panels="style settings">
                        <h3 class="v2-field-group-title">Padding</h3>
                        <div class="v2-spacing-grid">
                            <label class="v2-spacing-cell"><input id="v2PaddingTop" type="text" value="0"><span>TOP</span></label>
                            <label class="v2-spacing-cell"><input id="v2PaddingRight" type="text" value="0"><span>RIGHT</span></label>
                            <label class="v2-spacing-cell"><input id="v2PaddingBottom" type="text" value="0"><span>BTM</span></label>
                            <label class="v2-spacing-cell"><input id="v2PaddingLeft" type="text" value="0"><span>LFT</span></label>
                        </div>
                    </div>
                </div>

                <div class="v2-component-panel" id="v2ComponentPropertiesPanel" hidden>
                    <div id="v2ComponentFields"></div>
                </div>
            </div>

            <div class="v2-sidebar-footer">
                <button class="v2-btn v2-btn-primary" type="button" id="v2SaveBtn"><i class="mdi mdi-content-save-outline"></i> Save</button>
                <a class="v2-btn v2-btn-light" href="{{ route('admin.dynamic_landing_builder.pages') }}"><i class="mdi mdi-chevron-left"></i> Back to Dashboard</a>
            </div>
        </aside>

        <main class="v2-canvas">
            <div class="v2-canvas-inner">
                <div class="v2-alert v2-alert-info" id="v2Alert" role="alert"></div>

                <!-- <section class="v2-empty-section">
                    <div class="v2-component-actions">
                        <button class="v2-mini-btn" type="button" disabled title="Move up"><i class="mdi mdi-arrow-up"></i></button>
                        <button class="v2-mini-btn" type="button" disabled title="Move down"><i class="mdi mdi-arrow-down"></i></button>
                        <button class="v2-mini-btn" type="button" disabled title="Delete"><i class="mdi mdi-trash-can-outline"></i></button>
                    </div>
                    <span class="v2-empty-title">Empty Section</span>
                    <button class="v2-btn v2-btn-light" type="button" data-open-picker><i class="mdi mdi-plus"></i> Replace</button>
                </section> -->

                <!-- <button class="v2-add-circle" type="button" data-open-picker title="Add component"><i class="mdi mdi-plus"></i></button> -->

                <div id="v2CanvasComponents"></div>

                <button class="v2-add-bottom" type="button" data-open-picker>
                    <i class="mdi mdi-plus-box-outline" style="font-size: 26px;"></i>
                    <strong>Add New Component</strong>
                </button>
            </div>
        </main>
    </div>

    <div class="v2-picker" id="v2ComponentPicker" aria-hidden="true">
        <div class="v2-picker-backdrop" data-close-picker></div>
        <section class="v2-picker-panel" aria-labelledby="v2PickerTitle">
            <header class="v2-picker-head">
                <div>
                    <h3 class="v2-heading" id="v2PickerTitle">Components</h3>
                    <p class="v2-kicker">Select a block to add to your page</p>
                </div>
                <button class="v2-icon-btn" type="button" data-close-picker title="Close"><i class="mdi mdi-close"></i></button>
            </header>
            <div class="v2-picker-body">
                <div class="v2-component-grid" id="v2Catalog">
                    <div class="v2-kicker">Loading components...</div>
                </div>
            </div>
            <!-- <footer class="v2-picker-footer">
                <button class="v2-btn v2-btn-dark" style="width: 100%;" type="button" disabled>Import External Component</button>
            </footer> -->
        </section>
    </div>

    <script>
        (function () {
            const initialPageId = @json(request('page'));
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const routes = {
                pageBase: @json(url('admin/dynamic-landing-pages')),
                componentPreview: @json(url('admin/dynamic-landing-pages/__PAGE_ID__/components/__COMPONENT_ID__/preview')),
                catalogPreview: @json(url('admin/dynamic-landing-components/__COMPONENT_KEY__/preview')),
                catalog: @json(route('admin.dynamic_landing_components.index')),
                productOptions: @json(route('admin.dynamic_landing_products.options')),
                publish: @json(route('admin.dynamic_landing_pages.publish', ['page' => '__PAGE_ID__'])),
                dashboard: @json(route('admin.dynamic_landing_builder.pages'))
            };
            const state = {
                page: null,
                catalog: [],
                productOptions: [],
                selectedComponentId: null,
                editorMode: 'page',
                fieldErrors: {}
            };
            let productSearchTimer = null;
            const el = {
                alert: document.getElementById('v2Alert'),
                editingLabel: document.getElementById('v2EditingLabel'),
                pageName: document.getElementById('v2PageName'),
                pageSlug: document.getElementById('v2PageSlug'),
                seoTitle: document.getElementById('v2SeoTitle'),
                seoDescription: document.getElementById('v2SeoDescription'),
                pageStatus: document.getElementById('v2PageStatus'),
                publicUrl: document.getElementById('v2PublicUrl'),
                themePrimary: document.getElementById('v2ThemePrimary'),
                themeSecondary: document.getElementById('v2ThemeSecondary'),
                themeBackground: document.getElementById('v2ThemeBackground'),
                themeSurface: document.getElementById('v2ThemeSurface'),
                themeText: document.getElementById('v2ThemeText'),
                themeMutedText: document.getElementById('v2ThemeMutedText'),
                marginTop: document.getElementById('v2MarginTop'),
                marginRight: document.getElementById('v2MarginRight'),
                marginBottom: document.getElementById('v2MarginBottom'),
                marginLeft: document.getElementById('v2MarginLeft'),
                paddingTop: document.getElementById('v2PaddingTop'),
                paddingRight: document.getElementById('v2PaddingRight'),
                paddingBottom: document.getElementById('v2PaddingBottom'),
                paddingLeft: document.getElementById('v2PaddingLeft'),
                pagePropertyTabs: document.getElementById('v2PagePropertyTabs'),
                pagePropertiesPanel: document.getElementById('v2PagePropertiesPanel'),
                componentPropertiesPanel: document.getElementById('v2ComponentPropertiesPanel'),
                componentFields: document.getElementById('v2ComponentFields'),
                previewBtn: document.getElementById('v2PreviewBtn'),
                saveBtn: document.getElementById('v2SaveBtn'),
                publishBtn: document.getElementById('v2PublishBtn'),
                components: document.getElementById('v2CanvasComponents'),
                picker: document.getElementById('v2ComponentPicker'),
                catalog: document.getElementById('v2Catalog'),
                panelTabs: Array.from(document.querySelectorAll('[data-panel-tab]')),
                panels: Array.from(document.querySelectorAll('.v2-panel[data-panels]'))
            };

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function slugify(value) {
                return String(value || '')
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }

            function publishUrl(pageId) {
                return routes.publish.replace('__PAGE_ID__', encodeURIComponent(pageId));
            }

            function componentPreviewUrl(componentId) {
                const component = (state.page?.components || [])
                    .find((item) => String(item.id) === String(componentId));
                const url = routes.componentPreview
                    .replace('__PAGE_ID__', encodeURIComponent(state.page.id))
                    .replace('__COMPONENT_ID__', encodeURIComponent(componentId));

                if (!component?.updated_at) {
                    return url;
                }

                return `${url}?v=${encodeURIComponent(component.updated_at)}`;
            }

            function catalogPreviewUrl(componentKey) {
                return routes.catalogPreview.replace('__COMPONENT_KEY__', encodeURIComponent(componentKey));
            }

            async function requestJson(url, options = {}) {
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(options.headers || {})
                    },
                    ...options
                });
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const message = payload.message || Object.values(payload.errors || {}).flat()[0] || 'Request failed.';
                    const error = new Error(message);
                    error.errors = payload.errors || {};
                    throw error;
                }

                return payload;
            }

            function setAlert(message, type = 'info') {
                el.alert.className = `v2-alert v2-alert-${type} is-visible`;
                el.alert.textContent = message;
            }

            function clearAlert() {
                el.alert.className = 'v2-alert v2-alert-info';
                el.alert.textContent = '';
            }

            function setBusy(button, isBusy) {
                button.disabled = isBusy;
            }

            function showPanel(panelName) {
                el.panelTabs.forEach((tab) => {
                    tab.classList.toggle('is-active', tab.dataset.panelTab === panelName);
                });

                el.panels.forEach((panel) => {
                    const panels = String(panel.dataset.panels || '').split(/\s+/);
                    panel.hidden = !panels.includes(panelName);
                });
            }

            function showPageProperties(panelName = 'layout') {
                state.editorMode = 'page';
                el.pagePropertyTabs.hidden = false;
                el.pagePropertiesPanel.hidden = false;
                el.componentPropertiesPanel.hidden = true;
                el.saveBtn.innerHTML = '<i class="mdi mdi-content-save-outline"></i> Save';
                showPanel(panelName);
            }

            function showComponentProperties() {
                state.editorMode = 'component';
                el.pagePropertiesPanel.hidden = true;
                el.componentPropertiesPanel.hidden = false;
                el.saveBtn.innerHTML = '<i class="mdi mdi-content-save-outline"></i> Save Component';
            }

            function componentTitle(component) {
                const definition = state.catalog.find((item) => item.key === component.component_key);
                return definition?.name || component.component_key.replace(/[_-]+/g, ' ');
            }

            function componentDefinition(key) {
                return state.catalog.find((definition) => definition.key === key) || null;
            }

            function selectedComponent() {
                return (state.page?.components || [])
                    .find((component) => String(component.id) === String(state.selectedComponentId)) || null;
            }

            function pathParts(path) {
                return String(path).split('.').filter(Boolean);
            }

            function getPath(source, path, fallback = null) {
                return pathParts(path).reduce((value, part) => (
                    value && Object.prototype.hasOwnProperty.call(value, part) ? value[part] : fallback
                ), source);
            }

            function setPath(target, path, value) {
                const parts = pathParts(path);
                let cursor = target;

                parts.forEach((part, index) => {
                    if (index === parts.length - 1) {
                        cursor[part] = value;
                        return;
                    }

                    cursor[part] = cursor[part] && typeof cursor[part] === 'object' && !Array.isArray(cursor[part])
                        ? cursor[part]
                        : {};
                    cursor = cursor[part];
                });

                return target;
            }

            function mergeDeep(base, override) {
                const output = Array.isArray(base) ? [...base] : { ...(base || {}) };

                Object.entries(override || {}).forEach(([key, value]) => {
                    if (
                        value
                        && typeof value === 'object'
                        && !Array.isArray(value)
                        && output[key]
                        && typeof output[key] === 'object'
                        && !Array.isArray(output[key])
                    ) {
                        output[key] = mergeDeep(output[key], value);
                        return;
                    }

                    output[key] = value;
                });

                return output;
            }

            function fieldDefinitions(definition) {
                const schema = definition?.schema || {};
                return Object.entries(schema).flatMap(([section, fields]) => {
                    if (!fields || typeof fields !== 'object' || Array.isArray(fields)) {
                        return [];
                    }

                    return Object.entries(fields).map(([key, meta]) => ({
                        section,
                        key,
                        path: `${section}.${key}`,
                        meta: meta || {}
                    }));
                });
            }

            function resolvedComponentConfig(component, definition) {
                return mergeDeep(definition?.defaults || {}, component?.config || {});
            }

            function fieldError(path) {
                const direct = state.fieldErrors[path] || state.fieldErrors[`config.${path}`];

                if (direct) {
                    return Array.isArray(direct) ? direct.join(' ') : direct;
                }

                return Object.entries(state.fieldErrors)
                    .filter(([key]) => key.startsWith(`${path}.`) || key.startsWith(`config.${path}.`))
                    .flatMap(([, messages]) => Array.isArray(messages) ? messages : [messages])
                    .join(' ');
            }

            function isHexColor(value) {
                return typeof value === 'string' && /^#[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/.test(value.trim());
            }

            function inputValueFor(path, meta, config) {
                const value = getPath(config, path, meta.default ?? '');

                if (meta.type === 'product_selector') {
                    return Array.isArray(value) ? value.map((item) => Number(item)).filter((item) => item > 0) : [];
                }

                if (meta.type === 'category_selector') {
                    return Array.isArray(value) ? value.join(', ') : '';
                }

                if (meta.type === 'spacing_quad') {
                    const values = Array.isArray(value) ? value.slice(0, 4) : [];

                    while (values.length < 4) {
                        values.push('');
                    }

                    return values.map((item) => item ?? '');
                }

                if (['repeater', 'array', 'media'].includes(meta.type)) {
                    return JSON.stringify(value ?? (meta.type === 'media' ? null : []), null, 2);
                }

                if (meta.type === 'datetime' && typeof value === 'string') {
                    return value.replace(' ', 'T').slice(0, 16);
                }

                if (meta.type === 'color' && value && typeof value === 'object') {
                    return JSON.stringify(value, null, 2);
                }

                return value ?? '';
            }

            function parseEditorValue(input, meta) {
                if (meta.type === 'boolean') {
                    return input.checked;
                }

                if (meta.type === 'number') {
                    return input.value === '' ? null : Number(input.value);
                }

                if (meta.type === 'spacing_quad') {
                    const root = input.closest('.v2-spacing-quad');
                    const values = Array.from(root?.querySelectorAll('.v2-schema-field') || [])
                        .sort((a, b) => Number(a.dataset.spacingIndex || 0) - Number(b.dataset.spacingIndex || 0))
                        .map((field) => field.value.trim() || '0');

                    while (values.length < 4) {
                        values.push('0');
                    }

                    return values.slice(0, 4);
                }

                if (meta.type === 'product_selector') {
                    if (input.type === 'hidden') {
                        return JSON.parse(input.value || '[]')
                            .map((item) => Number(item))
                            .filter((item) => Number.isInteger(item) && item > 0);
                    }

                    if (input.multiple) {
                        return Array.from(input.selectedOptions)
                            .map((option) => Number(option.value))
                            .filter((item) => Number.isInteger(item) && item > 0);
                    }

                    const value = Number(input.value);

                    return Number.isInteger(value) && value > 0 ? [value] : [];
                }

                if (meta.type === 'category_selector') {
                    return input.value
                        .split(',')
                        .map((item) => Number(item.trim()))
                        .filter((item) => Number.isInteger(item) && item > 0);
                }

                if (['repeater', 'array', 'media'].includes(meta.type)) {
                    if (input.value.trim() === '') {
                        return meta.type === 'media' ? null : [];
                    }

                    return JSON.parse(input.value);
                }

                if (meta.type === 'color' && input.value.trim().startsWith('{')) {
                    return JSON.parse(input.value);
                }

                return input.value.trim() === '' ? null : input.value.trim();
            }

            function productLabel(product) {
                return product?.label || product?.name || `Product #${product?.id || ''}`;
            }

            function productPriceLabel(product) {
                return Number(product?.price || 0) > 0 ? Number(product.price).toFixed(2) : '';
            }

            function mergeProductOptions(products) {
                const byId = new Map(state.productOptions.map((product) => [Number(product.id), product]));

                (products || []).forEach((product) => {
                    byId.set(Number(product.id), product);
                });

                state.productOptions = [...byId.values()];
            }

            function productById(id) {
                return state.productOptions.find((product) => Number(product.id) === Number(id)) || { id, label: `Product #${id}` };
            }

            function productPickerIds(root) {
                return JSON.parse(root.querySelector('.v2-schema-field')?.value || '[]')
                    .map((item) => Number(item))
                    .filter((item) => Number.isInteger(item) && item > 0);
            }

            function setProductPickerIds(root, ids) {
                const multiple = root.dataset.multiple !== 'false';
                const cleanIds = [...new Set(ids
                    .map((item) => Number(item))
                    .filter((item) => Number.isInteger(item) && item > 0))];
                root.querySelector('.v2-schema-field').value = JSON.stringify(multiple ? cleanIds : cleanIds.slice(0, 1));
            }

            function renderProductPicker(root, options = state.productOptions) {
                const selectedIds = productPickerIds(root);
                const selected = root.querySelector('[data-product-selected]');
                const results = root.querySelector('[data-product-results]');
                const multiple = root.dataset.multiple !== 'false';

                selected.innerHTML = selectedIds.length
                    ? selectedIds.map((id) => {
                        const product = productById(id);
                        const price = productPriceLabel(product);

                        return `
                            <div class="v2-product-chip">
                                <span class="v2-product-name">
                                    <strong>${escapeHtml(productLabel(product))}</strong>
                                    ${price ? `<small>${escapeHtml(price)}</small>` : ''}
                                </span>
                                <button class="v2-mini-btn" type="button" data-remove-product-id="${escapeHtml(id)}" title="Remove product">
                                    <i class="mdi mdi-close"></i>
                                </button>
                            </div>
                        `;
                    }).join('')
                    : '<div class="v2-kicker">No product selected.</div>';

                results.innerHTML = (options || []).map((product) => {
                    const id = Number(product.id);
                    const isSelected = selectedIds.includes(id);
                    const price = productPriceLabel(product);

                    return `
                        <button class="v2-product-option ${isSelected ? 'is-selected' : ''}"
                                type="button"
                                data-select-product-id="${escapeHtml(id)}">
                            <span class="v2-product-name">
                                <strong>${escapeHtml(productLabel(product))}</strong>
                                ${price ? `<small>${escapeHtml(price)}</small>` : ''}
                            </span>
                            <i class="mdi ${isSelected ? 'mdi-check-circle-outline' : 'mdi-plus-circle-outline'}"></i>
                        </button>
                    `;
                }).join('') || '<div class="v2-kicker">No products found.</div>';

                if (!multiple && selectedIds.length) {
                    results.querySelectorAll('[data-select-product-id]').forEach((button) => {
                        if (!selectedIds.includes(Number(button.dataset.selectProductId))) {
                            button.classList.remove('is-selected');
                        }
                    });
                }
            }

            async function searchProductOptions(query, root) {
                const url = new URL(routes.productOptions, window.location.origin);

                if (query.trim() !== '') {
                    url.searchParams.set('q', query.trim());
                }

                const payload = await requestJson(url.toString());
                mergeProductOptions(payload.data || []);
                renderProductPicker(root, payload.data || []);
            }

            function repeaterValuesFor(field, config) {
                const value = getPath(config, field.path, field.meta.default ?? []);

                if (Array.isArray(value)) {
                    return value;
                }

                if (field.meta.type === 'media') {
                    return value ? [value] : [];
                }

                return [];
            }

            function repeaterKeysFor(field, rows) {
                if (Array.isArray(field.meta.fields) && field.meta.fields.length) {
                    return field.meta.fields.map((key) => String(key)).filter(Boolean);
                }

                const keys = new Set();
                const defaults = repeaterValuesFor(field, { [field.section]: { [field.key]: field.meta.default ?? [] } });

                [...rows, ...defaults].forEach((row) => {
                    if (row && typeof row === 'object' && !Array.isArray(row)) {
                        Object.keys(row).forEach((key) => {
                            if (!String(key).startsWith('__')) {
                                keys.add(key);
                            }
                        });
                    }
                });

                return [...keys];
            }

            function repeaterEmptyRow(keys) {
                if (!keys.length) {
                    return '';
                }

                return keys.reduce((row, key) => {
                    row[key] = '';
                    return row;
                }, {});
            }

            function repeaterTypesFor(rows, keys) {
                return keys.reduce((types, key) => {
                    const source = rows.find((row) => row && typeof row === 'object' && !Array.isArray(row) && row[key] !== null && row[key] !== undefined);
                    types[key] = typeof source?.[key] === 'number' ? 'number' : 'text';
                    return types;
                }, {});
            }

            function coerceRepeaterValue(value, type) {
                if (type === 'number') {
                    return value === '' ? null : Number(value);
                }

                return value;
            }

            function renderRepeaterRow(value, index, keys, types = {}) {
                const isObjectRow = keys.length > 0;

                return `
                    <div class="v2-repeater-row" data-repeater-row>
                        <div class="v2-repeater-row-head">
                            <span class="v2-repeater-row-title">Item ${index + 1}</span>
                            <button class="v2-mini-btn" type="button" data-remove-repeater-row title="Remove item">
                                <i class="mdi mdi-trash-can-outline"></i>
                            </button>
                        </div>
                        <div class="v2-repeater-grid">
                            ${isObjectRow
                                ? keys.map((key) => `
                                    <label>
                                        <span>${escapeHtml(key.replace(/[_-]+/g, ' '))}</span>
                                        <input type="${types[key] === 'number' ? 'number' : 'text'}"
                                               value="${escapeHtml(value?.[key] ?? '')}"
                                               data-repeater-key="${escapeHtml(key)}">
                                    </label>
                                `).join('')
                                : `
                                    <label>
                                        <span>Value</span>
                                        <input type="text" value="${escapeHtml(value ?? '')}" data-repeater-value>
                                    </label>
                                `}
                        </div>
                    </div>
                `;
            }

            function syncRepeaterValue(root) {
                const hidden = root.querySelector('.v2-schema-field[data-field-type]');
                const keys = JSON.parse(root.dataset.repeaterKeys || '[]');
                const types = JSON.parse(root.dataset.repeaterTypes || '{}');
                const previousRows = JSON.parse(hidden?.value || '[]');
                const rows = Array.from(root.querySelectorAll('[data-repeater-row]')).map((row, index) => {
                    if (!keys.length) {
                        return row.querySelector('[data-repeater-value]')?.value.trim() ?? '';
                    }

                    const previousRow = previousRows[index] && typeof previousRows[index] === 'object' && !Array.isArray(previousRows[index])
                        ? previousRows[index]
                        : {};
                    const item = keys.reduce((item, key) => {
                        const value = row.querySelector(`[data-repeater-key="${CSS.escape(key)}"]`)?.value.trim() ?? '';
                        item[key] = coerceRepeaterValue(value, types[key]);
                        return item;
                    }, {});

                    Object.keys(previousRow).forEach((key) => {
                        if (String(key).startsWith('__')) {
                            item[key] = previousRow[key];
                        }
                    });

                    if (keys.includes('price')) {
                        const price = String(item.price ?? '').trim();
                        const previousPrice = String(previousRow.price ?? '').trim();

                        if (price === '') {
                            delete item.__price_custom;
                        } else if (!previousRow.price || price !== previousPrice) {
                            item.__price_custom = true;
                        }
                    }

                    return item;
                });

                hidden.value = JSON.stringify(rows);
            }

            function renumberRepeaterRows(root) {
                root.querySelectorAll('[data-repeater-row]').forEach((row, index) => {
                    const title = row.querySelector('.v2-repeater-row-title');

                    if (title) {
                        title.textContent = `Item ${index + 1}`;
                    }
                });
            }

            function renderRepeaterInput(field, config) {
                const rows = repeaterValuesFor(field, config);
                const keys = repeaterKeysFor(field, rows);
                const types = repeaterTypesFor(rows, keys);
                const editableRows = rows.length ? rows : [repeaterEmptyRow(keys)];
                const common = `data-field-path="${escapeHtml(field.path)}" data-field-type="${escapeHtml(field.meta.type)}"`;

                return `
                    <div class="v2-repeater"
                         data-repeater
                         data-repeater-keys="${escapeHtml(JSON.stringify(keys))}"
                         data-repeater-types="${escapeHtml(JSON.stringify(types))}">
                        <input class="v2-schema-field" type="hidden" ${common} value="${escapeHtml(JSON.stringify(rows))}">
                        <div class="v2-repeater-rows">
                            ${editableRows.map((row, index) => renderRepeaterRow(row, index, keys, types)).join('')}
                        </div>
                        <button class="v2-btn v2-btn-light" type="button" data-add-repeater-row>
                            <i class="mdi mdi-plus"></i> Add Item
                        </button>
                    </div>
                `;
            }

            function renderSchemaInput(field, config) {
                const meta = field.meta;
                const type = meta.type || 'text';
                const value = inputValueFor(field.path, meta, config);
                const required = meta.required ? 'required' : '';
                const common = `data-field-path="${escapeHtml(field.path)}" data-field-type="${escapeHtml(type)}" ${required}`;

                if (type === 'textarea') {
                    return `<textarea class="v2-schema-field" ${common} rows="3">${escapeHtml(value)}</textarea>`;
                }

                if (type === 'select') {
                    const options = Array.isArray(meta.options) ? meta.options : [];
                    return `
                        <select class="v2-schema-field" ${common}>
                            ${options.map((option) => {
                                const selected = String(option) === String(value) ? 'selected' : '';
                                return `<option value="${escapeHtml(option)}" ${selected}>${escapeHtml(option)}</option>`;
                            }).join('')}
                        </select>
                    `;
                }

                if (type === 'boolean') {
                    return `
                        <label style="display:flex;align-items:center;gap:8px;font-weight:700;color:var(--v2-muted);">
                            <input class="v2-schema-field" type="checkbox" ${common} ${value ? 'checked' : ''}>
                            Enabled
                        </label>
                    `;
                }

                if (type === 'spacing_quad') {
                    const labels = Array.isArray(meta.labels) && meta.labels.length === 4
                        ? meta.labels
                        : ['Top', 'Right', 'Bottom', 'Left'];
                    const values = Array.isArray(value) ? value : ['', '', '', ''];

                    return `
                        <div class="v2-spacing-grid v2-spacing-quad">
                            ${labels.map((label, index) => `
                                <label class="v2-spacing-cell">
                                    <input class="v2-schema-field"
                                           type="text"
                                           value="${escapeHtml(values[index] ?? '')}"
                                           placeholder="0 or 24px"
                                           data-field-path="${escapeHtml(field.path)}"
                                           data-field-type="${escapeHtml(type)}"
                                           data-spacing-index="${index}">
                                    <span>${escapeHtml(label.slice(0, 5).toUpperCase())}</span>
                                </label>
                            `).join('')}
                        </div>
                    `;
                }

                if (type === 'product_selector') {
                    const selectedIds = Array.isArray(value) ? value.map((item) => Number(item)).filter((item) => item > 0) : [];
                    const optionsById = new Map(state.productOptions.map((product) => [Number(product.id), product]));

                    selectedIds.forEach((id) => {
                        if (!optionsById.has(id)) {
                            optionsById.set(id, { id, label: `Product #${id}` });
                        }
                    });

                    const options = [...optionsById.values()];

                    return `
                        <div class="v2-product-picker" data-product-picker data-multiple="${meta.multiple === false ? 'false' : 'true'}">
                            <input class="v2-schema-field" type="hidden" ${common} value="${escapeHtml(JSON.stringify(selectedIds))}">
                            <input class="v2-product-search" type="search" placeholder="Search products by name or SKU" data-product-search>
                            <div class="v2-product-selected" data-product-selected>
                                ${selectedIds.length
                                    ? selectedIds.map((id) => {
                                        const product = optionsById.get(Number(id)) || { id, label: `Product #${id}` };
                                        const price = productPriceLabel(product);

                                        return `
                                            <div class="v2-product-chip">
                                                <span class="v2-product-name">
                                                    <strong>${escapeHtml(productLabel(product))}</strong>
                                                    ${price ? `<small>${escapeHtml(price)}</small>` : ''}
                                                </span>
                                                <button class="v2-mini-btn" type="button" data-remove-product-id="${escapeHtml(id)}" title="Remove product">
                                                    <i class="mdi mdi-close"></i>
                                                </button>
                                            </div>
                                        `;
                                    }).join('')
                                    : '<div class="v2-kicker">No product selected.</div>'}
                            </div>
                            <div class="v2-product-results" data-product-results>
                                ${options.map((product) => {
                                    const id = Number(product.id);
                                    const isSelected = selectedIds.includes(id);
                                    const price = productPriceLabel(product);

                                    return `
                                        <button class="v2-product-option ${isSelected ? 'is-selected' : ''}"
                                                type="button"
                                                data-select-product-id="${escapeHtml(id)}">
                                            <span class="v2-product-name">
                                                <strong>${escapeHtml(productLabel(product))}</strong>
                                                ${price ? `<small>${escapeHtml(price)}</small>` : ''}
                                            </span>
                                            <i class="mdi ${isSelected ? 'mdi-check-circle-outline' : 'mdi-plus-circle-outline'}"></i>
                                        </button>
                                    `;
                                }).join('')}
                            </div>
                        </div>
                    `;
                }

                if (type === 'category_selector') {
                    return `<input class="v2-schema-field" ${common} type="text" value="${escapeHtml(value)}" placeholder="Comma separated IDs">`;
                }

                if (['repeater', 'array', 'media'].includes(type)) {
                    return renderRepeaterInput(field, config);
                }

                if (type === 'color') {
                    if (isHexColor(value)) {
                        return `<input class="v2-schema-field" ${common} type="color" value="${escapeHtml(value)}">`;
                    }

                    return `<input class="v2-schema-field" ${common} type="text" value="${escapeHtml(value)}" placeholder="#ffffff or theme token">`;
                }

                if (type === 'number') {
                    const min = meta.min !== undefined ? `min="${escapeHtml(meta.min)}"` : '';
                    const max = meta.max !== undefined ? `max="${escapeHtml(meta.max)}"` : '';
                    return `<input class="v2-schema-field" ${common} type="number" value="${escapeHtml(value)}" ${min} ${max}>`;
                }

                if (type === 'datetime') {
                    return `<input class="v2-schema-field" ${common} type="datetime-local" value="${escapeHtml(value)}">`;
                }

                if (type === 'url') {
                    return `<input class="v2-schema-field" ${common} type="url" value="${escapeHtml(value)}">`;
                }

                return `<input class="v2-schema-field" ${common} type="text" value="${escapeHtml(value)}">`;
            }

            function spacingQuadFrom(inputs) {
                return inputs.map((input) => input.value.trim() || '0');
            }

            function fillSpacingQuad(inputs, values) {
                const quad = Array.isArray(values) && values.length === 4 ? values : ['0', '0', '0', '0'];
                inputs.forEach((input, index) => {
                    input.value = quad[index] || '0';
                });
            }

            function marginInputs() {
                return [el.marginTop, el.marginRight, el.marginBottom, el.marginLeft];
            }

            function paddingInputs() {
                return [el.paddingTop, el.paddingRight, el.paddingBottom, el.paddingLeft];
            }

            function resizePreviewFrame(frame) {
                try {
                    const preview = frame.closest('.v2-component-preview');
                    const availableWidth = Math.max(preview?.clientWidth || frame.parentElement?.clientWidth || 1200, 1);
                    const designWidth = Math.max(1200, Math.round(availableWidth));
                    const scale = availableWidth / designWidth;

                    frame.style.width = `${designWidth}px`;
                    frame.style.transform = `scale(${scale})`;

                    const doc = frame.contentDocument || frame.contentWindow?.document;
                    const body = doc?.body;
                    const html = doc?.documentElement;
                    const height = Math.max(
                        body?.scrollHeight || 0,
                        body?.offsetHeight || 0,
                        html?.clientHeight || 0,
                        html?.scrollHeight || 0,
                        220
                    );

                    const clampedHeight = Math.min(Math.max(height, 220), 1800);
                    frame.style.height = `${clampedHeight}px`;

                    if (preview) {
                        preview.style.height = `${Math.ceil(clampedHeight * scale)}px`;
                    }
                } catch (error) {
                    frame.style.height = '520px';
                    frame.style.transform = 'none';
                }
            }

            function resizeAllPreviewFrames() {
                document.querySelectorAll('.v2-component-preview iframe')
                    .forEach((frame) => resizePreviewFrame(frame));
            }

            function reloadPreviewFrames() {
                document.querySelectorAll('.v2-component-preview iframe')
                    .forEach((frame) => {
                        const componentId = frame.closest('.v2-component[data-component-id]')?.dataset.componentId;

                        if (componentId) {
                            frame.src = componentPreviewUrl(componentId);
                        }
                    });
            }

            function reloadComponentPreview(componentId) {
                const frame = el.components.querySelector(`.v2-component[data-component-id="${componentId}"] iframe`);

                if (frame) {
                    frame.src = componentPreviewUrl(componentId);
                }
            }

            function settlePreviewFrame(frame) {
                resizePreviewFrame(frame);
                window.setTimeout(() => resizePreviewFrame(frame), 120);
                window.setTimeout(() => resizePreviewFrame(frame), 450);
            }

            window.dynamicLandingBuilderV2ResizePreview = resizePreviewFrame;
            window.dynamicLandingBuilderV2SettlePreview = settlePreviewFrame;

            function renderPageForm() {
                const page = state.page;
                const seo = page?.seo || {};
                const theme = page?.theme || {};
                el.editingLabel.textContent = page ? `Editing ${page.name}` : 'No page selected';
                el.pageName.value = page?.name || '';
                el.pageSlug.value = page?.slug || '';
                el.seoTitle.value = seo.title || '';
                el.seoDescription.value = seo.description || '';
                el.pageStatus.value = page?.status || '';
                el.publicUrl.value = page ? (page.public_url || `/landing/${page.slug}`) : '';
                el.themePrimary.value = theme.primary || '';
                el.themeSecondary.value = theme.secondary || '';
                el.themeBackground.value = theme.background || '';
                el.themeSurface.value = theme.surface || '';
                el.themeText.value = theme.text || '';
                el.themeMutedText.value = theme.muted_text || '';
                fillSpacingQuad(marginInputs(), theme.layout?.margin);
                fillSpacingQuad(paddingInputs(), theme.layout?.padding);
            }

            function renderComponentProperties() {
                const component = selectedComponent();
                const definition = component ? componentDefinition(component.component_key) : null;

                if (!component) {
                    el.componentFields.innerHTML = '<div class="v2-kicker">Select a section on the canvas to edit its fields.</div>';
                    return;
                }

                if (!definition) {
                    el.componentFields.innerHTML = '<div class="v2-kicker">Component definition is unavailable.</div>';
                    return;
                }

                const fields = fieldDefinitions(definition);
                const config = resolvedComponentConfig(component, definition);

                if (!fields.length) {
                    el.componentFields.innerHTML = '<div class="v2-kicker">This component has no configurable fields.</div>';
                    return;
                }

                const sections = fields.reduce((groups, field) => {
                    groups[field.section] = groups[field.section] || [];
                    groups[field.section].push(field);
                    return groups;
                }, {});

                el.componentFields.innerHTML = Object.entries(sections).map(([section, sectionFields]) => `
                    <section class="v2-field-group">
                        <h3 class="v2-field-group-title">${escapeHtml(section.replace(/_/g, ' '))}</h3>
                        ${sectionFields.map((field) => {
                            const meta = field.meta;
                            const help = meta.help || meta.description || '';
                            const error = fieldError(field.path);

                            return `
                                <div class="v2-field">
                                    <label>${escapeHtml(meta.label || field.key)}</label>
                                    ${renderSchemaInput(field, config)}
                                    ${help ? `<div class="v2-kicker">${escapeHtml(help)}</div>` : ''}
                                    ${error ? `<div class="v2-property-error">${escapeHtml(error)}</div>` : ''}
                                </div>
                            `;
                        }).join('')}
                    </section>
                `).join('');
            }

            function buildComponentConfigFromSidebar() {
                const component = selectedComponent();
                const definition = component ? componentDefinition(component.component_key) : null;

                if (!component || !definition) {
                    throw new Error('Component definition is unavailable.');
                }

                const config = {};

                fieldDefinitions(definition).forEach((field) => {
                    const input = Array.from(el.componentFields.querySelectorAll('.v2-schema-field'))
                        .find((candidate) => candidate.dataset.fieldPath === field.path);

                    if (!input) {
                        return;
                    }

                    setPath(config, field.path, parseEditorValue(input, field.meta));
                });

                return config;
            }

            function renderComponents() {
                const components = state.page?.components || [];

                if (!components.length) {
                    el.components.innerHTML = `
                        <section class="v2-component">
                            <div class="v2-component-body">
                                <div class="v2-component-key">empty-page</div>
                                <h2 class="v2-component-title">Start with a component</h2>
                                <p class="v2-component-copy">Use the add buttons to insert blocks from the dynamic landing component catalog.</p>
                                <button class="v2-btn v2-btn-primary v2-component-state" type="button" data-open-picker>
                                    <i class="mdi mdi-plus"></i> Add Component
                                </button>
                            </div>
                        </section>
                    `;
                    return;
                }

                el.components.innerHTML = components.map((component, index) => {
                    const active = state.selectedComponentId && String(state.selectedComponentId) === String(component.id) ? ' is-active' : '';
                    const disabledClass = component.is_enabled ? '' : ' opacity: .55;';

                    return `
                        <section class="v2-component${active}" data-component-id="${escapeHtml(component.id)}" style="${disabledClass}">
                            <div class="v2-component-label">
                                <i class="mdi mdi-drag"></i>
                                <span>${escapeHtml(componentTitle(component))}</span>
                            </div>
                            <div class="v2-component-actions">
                                <button class="v2-mini-btn v2-move-component" type="button" data-direction="up" data-component-id="${escapeHtml(component.id)}" ${index === 0 ? 'disabled' : ''} title="Move up">
                                    <i class="mdi mdi-arrow-up"></i>
                                </button>
                                <button class="v2-mini-btn v2-move-component" type="button" data-direction="down" data-component-id="${escapeHtml(component.id)}" ${index === components.length - 1 ? 'disabled' : ''} title="Move down">
                                    <i class="mdi mdi-arrow-down"></i>
                                </button>
                                <button class="v2-mini-btn v2-delete-component" type="button" data-component-id="${escapeHtml(component.id)}" title="Delete">
                                    <i class="mdi mdi-trash-can-outline"></i>
                                </button>
                            </div>
                            <div class="v2-component-preview">
                                <iframe
                                    src="${escapeHtml(componentPreviewUrl(component.id))}"
                                    title="${escapeHtml(componentTitle(component))} preview"
                                    loading="lazy"
                                    onload="window.dynamicLandingBuilderV2SettlePreview(this)"
                                ></iframe>
                            </div>
                        </section>
                    `;
                }).join('');

                window.requestAnimationFrame(resizeAllPreviewFrames);
            }

            function selectComponent(componentId) {
                state.selectedComponentId = componentId;
                state.fieldErrors = {};
                el.components.querySelectorAll('.v2-component[data-component-id]')
                    .forEach((component) => {
                        component.classList.toggle('is-active', String(component.dataset.componentId) === String(componentId));
                    });
                renderComponentProperties();
                showComponentProperties();
            }

            function renderCatalog() {
                if (!state.catalog.length) {
                    el.catalog.innerHTML = '<div class="v2-kicker">No components registered.</div>';
                    return;
                }

                el.catalog.innerHTML = state.catalog.map((definition) => `
                    <div class="v2-catalog-card" role="button" tabindex="0" data-component-key="${escapeHtml(definition.key)}">
                        <span class="v2-catalog-preview">
                            <iframe
                                src="${escapeHtml(catalogPreviewUrl(definition.key))}"
                                title="${escapeHtml(definition.name)} preview"
                                loading="lazy"
                                aria-hidden="true"
                            ></iframe>
                        </span>
                        <span class="v2-catalog-title">${escapeHtml(definition.name)}</span>
                        <span class="v2-catalog-meta">${escapeHtml(definition.category)} - ${escapeHtml(definition.key)}</span>
                    </div>
                `).join('');
            }

            function openPicker() {
                el.picker.classList.add('is-open');
                el.picker.setAttribute('aria-hidden', 'false');
            }

            function closePicker() {
                el.picker.classList.remove('is-open');
                el.picker.setAttribute('aria-hidden', 'true');
            }

            async function loadPage() {
                if (!initialPageId) {
                    setAlert('Missing page id. Open this builder with ?page=<id>.', 'warning');
                    return;
                }

                const payload = await requestJson(`${routes.pageBase}/${initialPageId}`);
                state.page = payload.data;
                state.selectedComponentId = null;
                renderPageForm();
                renderComponents();
                renderComponentProperties();
            }

            async function loadCatalog() {
                const payload = await requestJson(routes.catalog);
                state.catalog = payload.data || [];
                renderCatalog();
                renderComponents();
                renderComponentProperties();
            }

            async function loadProductOptions() {
                const payload = await requestJson(routes.productOptions);
                state.productOptions = payload.data || [];
                renderComponentProperties();
            }

            async function savePage() {
                if (!state.page) {
                    return false;
                }

                setBusy(el.saveBtn, true);
                clearAlert();

                try {
                    const payload = await requestJson(`${routes.pageBase}/${state.page.id}`, {
                        method: 'PATCH',
                        body: JSON.stringify({
                            name: el.pageName.value.trim(),
                            slug: slugify(el.pageSlug.value),
                            seo: {
                                title: el.seoTitle.value.trim(),
                                description: el.seoDescription.value.trim()
                            },
                            theme: {
                                primary: el.themePrimary.value.trim(),
                                secondary: el.themeSecondary.value.trim(),
                                background: el.themeBackground.value.trim(),
                                surface: el.themeSurface.value.trim(),
                                text: el.themeText.value.trim(),
                                muted_text: el.themeMutedText.value.trim(),
                                layout: {
                                    margin: spacingQuadFrom(marginInputs()),
                                    padding: spacingQuadFrom(paddingInputs())
                                }
                            }
                        })
                    });
                    state.page = { ...state.page, ...payload.data, components: state.page.components };
                    renderPageForm();
                    reloadPreviewFrames();
                    setAlert('Page saved.', 'success');
                    return true;
                } catch (error) {
                    setAlert(error.message, 'danger');
                    return false;
                } finally {
                    setBusy(el.saveBtn, false);
                }
            }

            async function saveComponent() {
                const component = selectedComponent();

                if (!state.page || !component) {
                    return false;
                }

                let config;

                try {
                    config = buildComponentConfigFromSidebar();
                } catch (error) {
                    setAlert('One or more component fields contain invalid values.', 'danger');
                    return false;
                }

                setBusy(el.saveBtn, true);
                clearAlert();

                try {
                    const payload = await requestJson(`${routes.pageBase}/${state.page.id}/components/${component.id}`, {
                        method: 'PATCH',
                        body: JSON.stringify({ config })
                    });
                    state.fieldErrors = {};
                    state.page.components = (state.page.components || []).map((item) => (
                        String(item.id) === String(component.id) ? payload.data : item
                    ));
                    renderComponentProperties();
                    reloadComponentPreview(component.id);
                    setAlert('Component saved.', 'success');
                    return true;
                } catch (error) {
                    state.fieldErrors = error.errors || {};
                    renderComponentProperties();
                    setAlert(error.message, 'danger');
                    return false;
                } finally {
                    setBusy(el.saveBtn, false);
                }
            }

            function saveCurrentContext() {
                return state.editorMode === 'component' ? saveComponent() : savePage();
            }

            async function publishPage() {
                if (!state.page) {
                    return;
                }

                setBusy(el.publishBtn, true);
                clearAlert();

                try {
                    const saved = await savePage();
                    if (!saved) {
                        return;
                    }
                    await requestJson(publishUrl(state.page.id), { method: 'POST' });
                    await loadPage();
                    setAlert('Page published.', 'success');
                } catch (error) {
                    setAlert(error.message, 'danger');
                } finally {
                    setBusy(el.publishBtn, false);
                }
            }

            async function addComponent(componentKey) {
                if (!state.page) {
                    setAlert('Select a page before adding components.', 'warning');
                    return;
                }

                const definition = state.catalog.find((item) => item.key === componentKey);

                try {
                    const payload = await requestJson(`${routes.pageBase}/${state.page.id}/components`, {
                        method: 'POST',
                        body: JSON.stringify({
                            component_key: componentKey,
                            config: definition?.defaults || {}
                        })
                    });
                    state.page.components = [...(state.page.components || []), payload.data];
                    renderComponents();
                    selectComponent(payload.data.id);
                    closePicker();
                    setAlert('Component added.', 'success');
                } catch (error) {
                    setAlert(error.message, 'danger');
                }
            }

            async function deleteComponent(componentId) {
                if (!state.page || !window.confirm('Delete this component from the page?')) {
                    return;
                }

                try {
                    await requestJson(`${routes.pageBase}/${state.page.id}/components/${componentId}`, {
                        method: 'DELETE'
                    });
                    state.page.components = (state.page.components || []).filter((component) => String(component.id) !== String(componentId));
                    if (String(state.selectedComponentId) === String(componentId)) {
                        state.selectedComponentId = null;
                        showPageProperties('layout');
                    }
                    renderComponents();
                    renderComponentProperties();
                    setAlert('Component deleted.', 'success');
                } catch (error) {
                    setAlert(error.message, 'danger');
                }
            }

            async function reorderComponents(componentId, direction) {
                const components = [...(state.page?.components || [])];
                const currentIndex = components.findIndex((component) => String(component.id) === String(componentId));
                const nextIndex = direction === 'up' ? currentIndex - 1 : currentIndex + 1;

                if (currentIndex < 0 || nextIndex < 0 || nextIndex >= components.length) {
                    return;
                }

                const [component] = components.splice(currentIndex, 1);
                components.splice(nextIndex, 0, component);
                state.page.components = components;
                state.selectedComponentId = componentId;
                renderComponents();

                try {
                    const payload = await requestJson(`${routes.pageBase}/${state.page.id}/components/reorder`, {
                        method: 'POST',
                        body: JSON.stringify({
                            component_ids: components.map((item) => item.id)
                        })
                    });
                    state.page.components = payload.data || components;
                    renderComponents();
                } catch (error) {
                    setAlert(error.message, 'danger');
                    await loadPage();
                }
            }

            el.saveBtn.addEventListener('click', saveCurrentContext);
            el.publishBtn.addEventListener('click', publishPage);
            el.previewBtn.addEventListener('click', () => {
                if (state.page?.preview_url) {
                    window.open(state.page.preview_url, '_blank', 'noopener');
                }
            });
            el.pageName.addEventListener('input', () => {
                el.editingLabel.textContent = el.pageName.value.trim() ? `Editing ${el.pageName.value.trim()}` : 'Editing draft page';
            });
            el.pageSlug.addEventListener('blur', () => {
                el.pageSlug.value = slugify(el.pageSlug.value);
            });
            el.panelTabs.forEach((tab) => {
                tab.addEventListener('click', () => showPageProperties(tab.dataset.panelTab));
            });
            el.componentFields.addEventListener('input', (event) => {
                const repeater = event.target.closest('[data-repeater]');

                if (repeater) {
                    syncRepeaterValue(repeater);
                }

                const productSearch = event.target.closest('[data-product-search]');

                if (productSearch) {
                    const picker = productSearch.closest('[data-product-picker]');
                    window.clearTimeout(productSearchTimer);
                    productSearchTimer = window.setTimeout(() => {
                        searchProductOptions(productSearch.value, picker)
                            .catch((error) => setAlert(error.message, 'danger'));
                    }, 250);
                }
            });
            document.addEventListener('click', (event) => {
                if (event.target.closest('[data-open-picker]')) {
                    openPicker();
                    return;
                }

                if (event.target.closest('[data-close-picker]')) {
                    closePicker();
                    return;
                }

                const selectProductButton = event.target.closest('[data-select-product-id]');
                if (selectProductButton) {
                    const picker = selectProductButton.closest('[data-product-picker]');
                    const selectedIds = productPickerIds(picker);
                    const productId = Number(selectProductButton.dataset.selectProductId);

                    setProductPickerIds(
                        picker,
                        picker.dataset.multiple === 'false' ? [productId] : [...selectedIds, productId]
                    );
                    renderProductPicker(picker);
                    return;
                }

                const removeProductButton = event.target.closest('[data-remove-product-id]');
                if (removeProductButton) {
                    const picker = removeProductButton.closest('[data-product-picker]');
                    const productId = Number(removeProductButton.dataset.removeProductId);

                    setProductPickerIds(picker, productPickerIds(picker).filter((id) => id !== productId));
                    renderProductPicker(picker);
                    return;
                }

                const addRepeaterButton = event.target.closest('[data-add-repeater-row]');
                if (addRepeaterButton) {
                    const repeater = addRepeaterButton.closest('[data-repeater]');
                    const rows = repeater?.querySelector('.v2-repeater-rows');
                    const keys = JSON.parse(repeater?.dataset.repeaterKeys || '[]');
                    const types = JSON.parse(repeater?.dataset.repeaterTypes || '{}');

                    if (repeater && rows) {
                        rows.insertAdjacentHTML('beforeend', renderRepeaterRow(repeaterEmptyRow(keys), rows.children.length, keys, types));
                        syncRepeaterValue(repeater);
                    }

                    return;
                }

                const removeRepeaterButton = event.target.closest('[data-remove-repeater-row]');
                if (removeRepeaterButton) {
                    const repeater = removeRepeaterButton.closest('[data-repeater]');
                    const row = removeRepeaterButton.closest('[data-repeater-row]');

                    row?.remove();

                    if (repeater && !repeater.querySelector('[data-repeater-row]')) {
                        const rows = repeater.querySelector('.v2-repeater-rows');
                        const keys = JSON.parse(repeater.dataset.repeaterKeys || '[]');
                        const types = JSON.parse(repeater.dataset.repeaterTypes || '{}');
                        rows?.insertAdjacentHTML('beforeend', renderRepeaterRow(repeaterEmptyRow(keys), 0, keys, types));
                    }

                    if (repeater) {
                        renumberRepeaterRows(repeater);
                        syncRepeaterValue(repeater);
                    }

                    return;
                }

                const catalogCard = event.target.closest('.v2-catalog-card');
                if (catalogCard) {
                    addComponent(catalogCard.dataset.componentKey);
                    return;
                }

                const deleteButton = event.target.closest('.v2-delete-component');
                if (deleteButton) {
                    deleteComponent(deleteButton.dataset.componentId);
                    return;
                }

                const moveButton = event.target.closest('.v2-move-component');
                if (moveButton) {
                    reorderComponents(moveButton.dataset.componentId, moveButton.dataset.direction);
                    return;
                }

                const component = event.target.closest('.v2-component[data-component-id]');
                if (component) {
                    selectComponent(component.dataset.componentId);
                }
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closePicker();
                }

                if ((event.key === 'Enter' || event.key === ' ') && event.target.closest('.v2-catalog-card')) {
                    event.preventDefault();
                    addComponent(event.target.closest('.v2-catalog-card').dataset.componentKey);
                }
            });
            window.addEventListener('resize', resizeAllPreviewFrames);
            showPageProperties('layout');

            Promise.all([loadCatalog(), loadProductOptions(), loadPage()])
                .catch((error) => setAlert(error.message, 'danger'));
        })();
    </script>
</body>
</html>
