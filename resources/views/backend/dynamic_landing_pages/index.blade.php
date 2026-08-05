@extends('backend.app')

@push('css')
<style>
    .dlp-shell {
        --dlp-ink: #132017;
        --dlp-muted: #64748b;
        --dlp-line: #dce6dd;
        --dlp-soft: #f5f8f3;
        --dlp-green: #147a32;
        --dlp-green-dark: #0b5d25;
        --dlp-gold: #f4b000;
        --dlp-card: #ffffff;
        max-width: 1480px;
        margin: 0 auto;
    }

    .dlp-shell .page-title-box {
        margin-bottom: 14px;
    }

    .dlp-hero {
        background:
            linear-gradient(135deg, rgba(20, 122, 50, .98), rgba(12, 83, 35, .96)),
            repeating-linear-gradient(45deg, rgba(255,255,255,.08) 0 1px, transparent 1px 12px);
        border-radius: 8px;
        color: #fff;
        margin-bottom: 18px;
        overflow: hidden;
        padding: 24px;
        position: relative;
        box-shadow: 0 18px 45px rgba(12, 83, 35, .18);
    }

    .dlp-hero::after {
        content: "";
        position: absolute;
        inset: auto -40px -70px auto;
        width: 260px;
        height: 180px;
        background: rgba(244, 176, 0, .18);
        transform: rotate(-12deg);
    }

    .dlp-hero-inner {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 24px;
        position: relative;
        z-index: 1;
    }

    .dlp-eyebrow {
        align-items: center;
        display: flex;
        gap: 8px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .08em;
        margin-bottom: 8px;
        text-transform: uppercase;
    }

    .dlp-eyebrow i {
        color: var(--dlp-gold);
        font-size: 18px;
    }

    .dlp-hero h3 {
        color: #fff;
        font-size: 28px;
        font-weight: 800;
        line-height: 1.15;
        margin: 0 0 8px;
    }

    .dlp-hero p {
        color: rgba(255,255,255,.82);
        font-size: 14px;
        margin: 0;
        max-width: 620px;
    }

    .dlp-build-btn {
        align-items: center;
        background: #fff;
        border: 0;
        border-radius: 8px;
        box-shadow: 0 16px 30px rgba(0, 0, 0, .18);
        color: var(--dlp-green-dark);
        display: inline-flex;
        font-weight: 800;
        gap: 8px;
        min-height: 44px;
        padding: 0 18px;
        white-space: nowrap;
    }

    .dlp-build-btn:hover,
    .dlp-build-btn:focus {
        background: #fff7df;
        color: var(--dlp-green-dark);
    }

    .dlp-board {
        background: var(--dlp-card);
        border: 1px solid var(--dlp-line);
        border-radius: 8px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
        overflow: hidden;
    }

    .dlp-board-head {
        display: grid;
        gap: 18px;
        grid-template-columns: minmax(0, 1fr) auto;
        padding: 18px;
    }

    .dlp-board-title {
        color: var(--dlp-ink);
        font-size: 18px;
        font-weight: 800;
        margin: 0 0 4px;
    }

    .dlp-board-copy {
        color: var(--dlp-muted);
        font-size: 13px;
        margin: 0;
    }

    .dlp-tools {
        align-items: center;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .dlp-search {
        min-width: 260px;
        position: relative;
    }

    .dlp-search i {
        color: #94a3b8;
        font-size: 16px;
        left: 12px;
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
    }

    .dlp-search input {
        border: 1px solid var(--dlp-line);
        border-radius: 8px;
        height: 40px;
        padding-left: 36px;
    }

    .dlp-filter {
        border: 1px solid var(--dlp-line);
        border-radius: 8px;
        color: var(--dlp-ink);
        height: 40px;
        min-width: 140px;
    }

    .dlp-stats {
        border-top: 1px solid var(--dlp-line);
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .dlp-stat {
        background: linear-gradient(180deg, #fff, #fbfdf9);
        border-right: 1px solid var(--dlp-line);
        padding: 14px 18px;
    }

    .dlp-stat:last-child {
        border-right: 0;
    }

    .dlp-stat-label {
        color: var(--dlp-muted);
        display: block;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .06em;
        margin-bottom: 4px;
        text-transform: uppercase;
    }

    .dlp-stat-value {
        color: var(--dlp-ink);
        font-size: 22px;
        font-weight: 800;
        line-height: 1;
    }

    .dlp-pages-wrap {
        background: #f8faf8;
        border-top: 1px solid var(--dlp-line);
        padding: 18px;
    }

    .dlp-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 18px;
    }

    .dlp-card {
        background: #fff;
        border: 1px solid var(--dlp-line);
        border-radius: 8px;
        overflow: hidden;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .dlp-card:hover {
        border-color: rgba(20, 122, 50, .4);
        box-shadow: 0 18px 36px rgba(15, 23, 42, .1);
        transform: translateY(-2px);
    }

    .dlp-preview {
        background: #eef4ee;
        border-bottom: 1px solid var(--dlp-line);
        height: 235px;
        overflow: hidden;
        position: relative;
    }

    .dlp-preview::before {
        background: linear-gradient(180deg, rgba(255,255,255,.9), rgba(255,255,255,.55));
        border-bottom: 1px solid rgba(0,0,0,.06);
        content: "";
        height: 22px;
        left: 0;
        position: absolute;
        right: 0;
        top: 0;
        z-index: 2;
    }

    .dlp-preview iframe {
        width: 1440px;
        height: 1040px;
        border: 0;
        transform: scale(.245);
        transform-origin: top left;
        pointer-events: none;
        background: #fff;
    }

    .dlp-preview-empty {
        height: 100%;
        display: grid;
        place-items: center;
        color: var(--dlp-muted);
        font-size: 13px;
        text-align: center;
        padding: 24px;
    }

    .dlp-card-body {
        padding: 16px;
    }

    .dlp-card-title {
        align-items: flex-start;
        color: var(--dlp-ink);
        display: flex;
        font-size: 16px;
        font-weight: 800;
        gap: 10px;
        justify-content: space-between;
        margin: 0 0 6px;
    }

    .dlp-title-text {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dlp-status {
        align-items: center;
        border-radius: 999px;
        display: inline-flex;
        flex: 0 0 auto;
        font-size: 11px;
        font-weight: 800;
        min-height: 24px;
        padding: 0 9px;
        text-transform: capitalize;
    }

    .dlp-status-published {
        background: #e7f8ec;
        color: #12642c;
    }

    .dlp-status-draft {
        background: #fff5d7;
        color: #8a5a00;
    }

    .dlp-status-archived {
        background: #eef2f7;
        color: #475569;
    }

    .dlp-card-meta {
        color: var(--dlp-muted);
        font-size: 12px;
        margin-bottom: 14px;
    }

    .dlp-url {
        align-items: center;
        background: #f8fafc;
        border: 1px solid #edf2f7;
        border-radius: 8px;
        color: #334155;
        display: grid;
        font-size: 12px;
        gap: 0;
        grid-template-columns: auto minmax(0, 1fr) auto;
        margin-bottom: 14px;
        min-height: 34px;
        overflow: hidden;
        padding: 4px;
    }

    .dlp-url-prefix {
        align-items: center;
        color: #64748b;
        display: inline-flex;
        gap: 6px;
        min-height: 32px;
        padding: 0 8px;
        white-space: nowrap;
    }

    .dlp-url input {
        background: #fff;
        border: 1px solid transparent;
        border-radius: 6px;
        color: #132017;
        font-size: 12px;
        height: 32px;
        min-width: 0;
        padding: 0 8px;
    }

    .dlp-url input:focus {
        border-color: rgba(20, 122, 50, .45);
        box-shadow: 0 0 0 .14rem rgba(20, 122, 50, .1);
        outline: 0;
    }

    .dlp-url-save {
        align-items: center;
        background: #e7f8ec;
        border: 1px solid #c5ecd0;
        border-radius: 6px;
        color: var(--dlp-green-dark);
        display: inline-flex;
        font-size: 16px;
        justify-content: center;
        min-height: 32px;
        min-width: 34px;
    }

    .dlp-url-save:hover,
    .dlp-url-save:focus {
        background: #d8f2df;
        color: var(--dlp-green-dark);
    }

    .dlp-url input[disabled],
    .dlp-url-save[disabled] {
        cursor: wait;
        opacity: .65;
    }

    .dlp-url span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dlp-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .dlp-actions .btn {
        align-items: center;
        border-radius: 8px;
        display: inline-flex;
        gap: 5px;
        min-height: 34px;
    }

    .dlp-actions .btn-primary {
        background: var(--dlp-green);
        border-color: var(--dlp-green);
        box-shadow: 0 8px 18px rgba(20, 122, 50, .16);
    }

    .dlp-actions .btn-light {
        background: #f8fafc;
        border-color: #edf2f7;
        color: #334155;
    }

    .dlp-actions .btn-danger-soft {
        background: #fff1f2;
        border-color: #ffe4e6;
        color: #be123c;
    }

    .dlp-actions .btn-danger-soft:hover,
    .dlp-actions .btn-danger-soft:focus {
        background: #ffe4e6;
        border-color: #fecdd3;
        color: #9f1239;
    }

    .dlp-empty {
        background: #fff;
        border: 1px dashed #cbd5d1;
        border-radius: 8px;
        color: var(--dlp-muted);
        padding: 36px;
        text-align: center;
    }

    .dlp-empty i {
        color: var(--dlp-green);
        display: block;
        font-size: 30px;
        margin-bottom: 8px;
    }

    @media (max-width: 767px) {
        .dlp-hero {
            padding: 18px;
        }

        .dlp-hero-inner,
        .dlp-board-head,
        .dlp-tools {
            align-items: stretch;
            flex-direction: column;
            display: flex;
        }

        .dlp-hero h3 {
            font-size: 22px;
        }

        .dlp-search,
        .dlp-filter {
            min-width: 100%;
            width: 100%;
        }

        .dlp-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dlp-stat {
            border-bottom: 1px solid var(--dlp-line);
        }

        .dlp-preview {
            height: 220px;
        }
    }
</style>
@endpush

@section('content')
<div class="dlp-shell">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Page Builder</li>
                    </ol>
                </div>
                <h4 class="page-title">Page Builder</h4>
            </div>
        </div>
    </div>

    <div class="alert d-none" id="dlpAlert" role="alert"></div>

    <section class="dlp-hero" aria-labelledby="dlpHeroTitle">
        <div class="dlp-hero-inner">
            <div>
                <div class="dlp-eyebrow"><i class="mdi mdi-leaf"></i> Landing Page Studio</div>
                <h3 id="dlpHeroTitle">Build, preview, and publish campaign pages</h3>
                <p>Manage every dynamic landing page from one place with live previews, quick editing, and clean public URLs.</p>
            </div>
            <button class="btn dlp-build-btn" type="button" id="dlpBuildNewPageBtn">
                <i class="mdi mdi-plus-circle-outline"></i>
                <span>+ Build New Page</span>
            </button>
        </div>
    </section>

    <section class="dlp-board">
        <div class="dlp-board-head">
            <div>
                <h5 class="dlp-board-title">Built Pages</h5>
                <p class="dlp-board-copy">Search pages, check publish state, and open the builder without losing context.</p>
            </div>
            <div class="dlp-tools">
                <label class="dlp-search" for="dlpSearch">
                    <i class="mdi mdi-magnify"></i>
                    <input class="form-control" id="dlpSearch" type="search" placeholder="Search pages or paths">
                </label>
                <select class="form-select dlp-filter" id="dlpStatusFilter" aria-label="Filter by status">
                    <option value="all">All statuses</option>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
        </div>
        <div class="dlp-stats" aria-label="Page builder summary">
            <div class="dlp-stat">
                <span class="dlp-stat-label">Total Pages</span>
                <strong class="dlp-stat-value" id="dlpTotalCount">0</strong>
            </div>
            <div class="dlp-stat">
                <span class="dlp-stat-label">Published</span>
                <strong class="dlp-stat-value" id="dlpPublishedCount">0</strong>
            </div>
            <div class="dlp-stat">
                <span class="dlp-stat-label">Drafts</span>
                <strong class="dlp-stat-value" id="dlpDraftCount">0</strong>
            </div>
            <div class="dlp-stat">
                <span class="dlp-stat-label">Components</span>
                <strong class="dlp-stat-value" id="dlpComponentCount">0</strong>
            </div>
        </div>
        <div class="dlp-pages-wrap" id="dlpPages">
            <div class="dlp-empty"><i class="mdi mdi-loading mdi-spin"></i>Loading pages...</div>
        </div>
    </section>
</div>
@endsection

@push('script')
<script>
(function () {
    const routes = {
        pages: @json(route('admin.dynamic_landing_pages.index')),
        pageStore: @json(route('admin.dynamic_landing_pages.store')),
        pageUpdate: @json(route('admin.dynamic_landing_pages.update', ['page' => '__PAGE_ID__'])),
        pageDestroy: @json(route('admin.dynamic_landing_pages.destroy', ['page' => '__PAGE_ID__'])),
        builder: @json(route('admin.dynamic_landing_builder.v2'))
    };
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const state = {
        pages: [],
        search: '',
        status: 'all'
    };
    const el = {
        alert: document.getElementById('dlpAlert'),
        pages: document.getElementById('dlpPages'),
        buildNewPageBtn: document.getElementById('dlpBuildNewPageBtn'),
        search: document.getElementById('dlpSearch'),
        statusFilter: document.getElementById('dlpStatusFilter'),
        totalCount: document.getElementById('dlpTotalCount'),
        publishedCount: document.getElementById('dlpPublishedCount'),
        draftCount: document.getElementById('dlpDraftCount'),
        componentCount: document.getElementById('dlpComponentCount')
    };

    function setAlert(message, type = 'info') {
        el.alert.className = `alert alert-${type}`;
        el.alert.textContent = message;
    }

    function clearAlert() {
        el.alert.className = 'alert d-none';
        el.alert.textContent = '';
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function randomSlug() {
        const token = Math.random().toString(36).slice(2, 8);
        return `page-${Date.now().toString(36)}-${token}`;
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
            throw new Error(message);
        }

        return payload;
    }

    function builderUrl(pageId) {
        const url = new URL(routes.builder, window.location.origin);
        url.searchParams.set('page', pageId);
        return url.toString();
    }

    function destroyUrl(pageId) {
        return routes.pageDestroy.replace('__PAGE_ID__', encodeURIComponent(pageId));
    }

    function updateUrl(pageId) {
        return routes.pageUpdate.replace('__PAGE_ID__', encodeURIComponent(pageId));
    }

    function publicPath(page) {
        return `/landing/${page.slug}`;
    }

    function slugify(value) {
        return String(value || '')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    function statusClass(status) {
        return String(status || 'draft').toLowerCase().replace(/[^a-z0-9_-]/g, '-');
    }

    function findSlugInput(pageId) {
        return Array.from(el.pages.querySelectorAll('.dlp-slug-input'))
            .find((input) => String(input.dataset.pageId) === String(pageId));
    }

    function findSlugButton(pageId) {
        return Array.from(el.pages.querySelectorAll('.dlp-save-slug'))
            .find((button) => String(button.dataset.pageId) === String(pageId));
    }

    function updateStats(pages) {
        el.totalCount.textContent = pages.length;
        el.publishedCount.textContent = pages.filter((page) => page.status === 'published').length;
        el.draftCount.textContent = pages.filter((page) => page.status === 'draft').length;
        el.componentCount.textContent = pages.reduce((total, page) => total + Number(page.components_count || 0), 0);
    }

    function filteredPages() {
        const search = state.search.trim().toLowerCase();
        return state.pages.filter((page) => {
            const matchesStatus = state.status === 'all' || page.status === state.status;
            const haystack = `${page.name} ${page.slug} ${publicPath(page)}`.toLowerCase();
            const matchesSearch = !search || haystack.includes(search);
            return matchesStatus && matchesSearch;
        });
    }

    function renderPages() {
        const pages = filteredPages();
        updateStats(state.pages);

        if (!state.pages.length) {
            el.pages.innerHTML = `
                <div class="dlp-empty">
                    <i class="mdi mdi-file-plus-outline"></i>
                    No built pages yet. Start with Build New Page.
                </div>
            `;
            return;
        }

        if (!pages.length) {
            el.pages.innerHTML = `
                <div class="dlp-empty">
                    <i class="mdi mdi-file-search-outline"></i>
                    No pages match the current filters.
                </div>
            `;
            return;
        }

        el.pages.innerHTML = `
            <div class="dlp-grid">
                ${pages.map((page) => `
                    <article class="dlp-card">
                        <div class="dlp-preview">
                            ${page.preview_url
                                ? `<iframe src="${escapeHtml(page.preview_url)}" loading="lazy" title="${escapeHtml(page.name)} preview"></iframe>`
                                : '<div class="dlp-preview-empty">Preview unavailable</div>'}
                        </div>
                        <div class="dlp-card-body">
                            <h6 class="dlp-card-title">
                                <span class="dlp-title-text">${escapeHtml(page.name)}</span>
                                <span class="dlp-status dlp-status-${statusClass(page.status)}">${escapeHtml(page.status)}</span>
                            </h6>
                            <div class="dlp-card-meta">
                                ${Number(page.components_count || 0)} components
                            </div>
                            <div class="dlp-url" title="${escapeHtml(publicPath(page))}">
                                <span class="dlp-url-prefix">
                                    <i class="mdi mdi-link-variant"></i>
                                    /landing/
                                </span>
                                <input class="dlp-slug-input" type="text" value="${escapeHtml(page.slug)}" data-page-id="${escapeHtml(page.id)}" aria-label="${escapeHtml(page.name)} public path slug">
                                <button class="dlp-url-save dlp-save-slug" type="button" data-page-id="${escapeHtml(page.id)}" title="Save public path">
                                    <i class="mdi mdi-content-save-outline"></i>
                                </button>
                            </div>
                            <div class="dlp-actions">
                                <a class="btn btn-sm btn-primary" href="${escapeHtml(builderUrl(page.id))}" target="_blank" rel="noopener">
                                    <i class="mdi mdi-pencil-outline"></i> Build
                                </a>
                                ${page.preview_url ? `
                                    <a class="btn btn-sm btn-light" href="${escapeHtml(page.preview_url)}" target="_blank" rel="noopener">
                                        <i class="mdi mdi-eye-outline"></i> Preview
                                    </a>
                                ` : ''}
                                ${page.public_url ? `
                                    <a class="btn btn-sm btn-light" href="${escapeHtml(page.public_url)}" target="_blank" rel="noopener">
                                        <i class="mdi mdi-open-in-new"></i> Public
                                    </a>
                                ` : ''}
                                <button class="btn btn-sm btn-danger-soft dlp-delete-page" type="button" data-page-id="${escapeHtml(page.id)}" data-page-name="${escapeHtml(page.name)}">
                                    <i class="mdi mdi-trash-can-outline"></i> Delete
                                </button>
                            </div>
                        </div>
                    </article>
                `).join('')}
            </div>
        `;
    }

    async function loadPages() {
        clearAlert();
        const url = new URL(routes.pages, window.location.origin);
        url.searchParams.set('per_page', '100');
        const payload = await requestJson(url.toString());
        state.pages = payload.data || [];
        renderPages();
    }

    async function buildNewPage() {
        clearAlert();
        const slug = randomSlug();

        el.buildNewPageBtn.disabled = true;

        try {
            const payload = await requestJson(routes.pageStore, {
                method: 'POST',
                body: JSON.stringify({
                    name: `Page ${slug.replace(/^page-/, '')}`,
                    slug
                })
            });

            const targetUrl = builderUrl(payload.data.id);
            window.open(targetUrl, '_blank', 'noopener');
            await loadPages();
        } catch (error) {
            setAlert(error.message, 'danger');
        } finally {
            el.buildNewPageBtn.disabled = false;
        }
    }

    async function deletePage(pageId, pageName, button) {
        const confirmed = window.confirm(`Delete "${pageName}"? This will remove the page and its builder data.`);

        if (!confirmed) {
            return;
        }

        clearAlert();
        button.disabled = true;

        try {
            await requestJson(destroyUrl(pageId), {
                method: 'DELETE'
            });

            state.pages = state.pages.filter((page) => String(page.id) !== String(pageId));
            renderPages();
            setAlert('Page deleted successfully.', 'success');
        } catch (error) {
            button.disabled = false;
            setAlert(error.message, 'danger');
        }
    }

    async function saveSlug(pageId, input, button) {
        const page = state.pages.find((item) => String(item.id) === String(pageId));
        const slug = slugify(input.value);

        input.value = slug;

        if (!page || slug === page.slug) {
            return;
        }

        if (!slug) {
            setAlert('Public path slug is required.', 'warning');
            return;
        }

        clearAlert();
        input.disabled = true;
        button.disabled = true;

        try {
            const payload = await requestJson(updateUrl(pageId), {
                method: 'PATCH',
                body: JSON.stringify({ slug })
            });
            state.pages = state.pages.map((item) => String(item.id) === String(pageId) ? payload.data : item);
            renderPages();
            setAlert('Public path updated.', 'success');
        } catch (error) {
            input.disabled = false;
            button.disabled = false;
            input.value = page.slug;
            setAlert(error.message, 'danger');
        }
    }

    el.buildNewPageBtn.addEventListener('click', buildNewPage);
    el.pages.addEventListener('click', (event) => {
        const deleteButton = event.target.closest('.dlp-delete-page');

        if (deleteButton) {
            deletePage(deleteButton.dataset.pageId, deleteButton.dataset.pageName, deleteButton);
            return;
        }

        const slugButton = event.target.closest('.dlp-save-slug');

        if (slugButton) {
            const input = findSlugInput(slugButton.dataset.pageId);

            if (input) {
                saveSlug(slugButton.dataset.pageId, input, slugButton);
            }
        }
    });
    el.pages.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' || !event.target.classList.contains('dlp-slug-input')) {
            return;
        }

        event.preventDefault();
        const input = event.target;
        const button = findSlugButton(input.dataset.pageId);

        if (button) {
            saveSlug(input.dataset.pageId, input, button);
        }
    });
    el.search.addEventListener('input', (event) => {
        state.search = event.target.value;
        renderPages();
    });
    el.statusFilter.addEventListener('change', (event) => {
        state.status = event.target.value;
        renderPages();
    });
    loadPages().catch((error) => setAlert(error.message, 'danger'));
})();
</script>
@endpush
