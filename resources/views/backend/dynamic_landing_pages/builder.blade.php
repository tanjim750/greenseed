@extends('backend.app')

@push('css')
<style>
    .dlb-shell .card {
        border-radius: 8px;
    }

    .dlb-page-list,
    .dlb-component-list,
    .dlb-catalog-list {
        max-height: 620px;
        overflow: auto;
    }

    .dlb-page-item,
    .dlb-component-item,
    .dlb-catalog-item {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        cursor: pointer;
        padding: 12px;
        transition: border-color .15s ease, background-color .15s ease;
    }

    .dlb-page-item:hover,
    .dlb-component-item:hover,
    .dlb-catalog-item:hover {
        border-color: #98a6ad;
        background: #f8fafc;
    }

    .dlb-page-item.active,
    .dlb-component-item.active {
        border-color: #0acf97;
        background: #effaf6;
    }

    .dlb-muted {
        color: #6c757d;
        font-size: 12px;
    }

    .dlb-config-textarea {
        min-height: 300px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size: 12px;
        line-height: 1.55;
    }

    .dlb-schema-box {
        max-height: 250px;
        overflow: auto;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .dlb-spacing-quad {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
    }

    .dlb-spacing-quad label {
        margin: 0;
    }

    .dlb-spacing-quad span {
        display: block;
        color: #6c757d;
        font-size: 11px;
        margin-bottom: 4px;
    }

    .dlb-toolbar {
        gap: 8px;
    }

    .dlb-empty {
        border: 1px dashed #d1d5db;
        border-radius: 8px;
        color: #6c757d;
        padding: 22px;
        text-align: center;
    }

    .dlb-property-section {
        border-top: 1px solid #eef2f7;
        padding-top: 14px;
    }

    .dlb-property-section:first-child {
        border-top: 0;
        padding-top: 0;
    }

    .dlb-property-error {
        color: #fa5c7c;
        font-size: 12px;
        margin-top: 4px;
    }

    .dlb-field-meta {
        color: #98a6ad;
        font-size: 11px;
    }

    .dlb-editor-mode .btn {
        min-width: 84px;
    }

    .dlb-canvas-shell {
        background: #f3f6f9;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 16px;
    }

    .dlb-canvas {
        background: #ffffff;
        border: 1px solid #dbe3ea;
        border-radius: 8px;
        margin: 0 auto;
        max-width: 100%;
        min-height: 360px;
        padding: 14px;
        transition: max-width .15s ease;
    }

    .dlb-canvas[data-viewport="desktop"] {
        max-width: 100%;
    }

    .dlb-canvas[data-viewport="tablet"] {
        max-width: 760px;
    }

    .dlb-canvas[data-viewport="mobile"] {
        max-width: 390px;
    }

    .dlb-component-summary {
        background: #f8fafc;
        border-radius: 6px;
        color: #475467;
        font-size: 12px;
        margin-top: 10px;
        padding: 8px;
    }

    .dlb-catalog-item[draggable="true"],
    .dlb-component-item[draggable="true"] {
        cursor: grab;
    }

    .dlb-catalog-item[draggable="true"]:active,
    .dlb-component-item[draggable="true"]:active {
        cursor: grabbing;
    }

    .dlb-save-status {
        align-items: center;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 999px;
        color: #475467;
        display: inline-flex;
        font-size: 12px;
        gap: 6px;
        min-height: 30px;
        padding: 5px 10px;
    }

    .dlb-save-status[data-state="dirty"] {
        background: #fff8e5;
        border-color: #ffe08a;
        color: #946200;
    }

    .dlb-save-status[data-state="saving"] {
        background: #e8f3ff;
        border-color: #b8ddff;
        color: #095c9f;
    }

    .dlb-save-status[data-state="error"] {
        background: #fff1f3;
        border-color: #ffd0d9;
        color: #b42318;
    }

    .dlb-save-status[data-state="saved"] {
        background: #effaf6;
        border-color: #bcebdc;
        color: #087a5a;
    }

    @media (max-width: 991.98px) {
        .dlb-page-list,
        .dlb-component-list,
        .dlb-catalog-list {
            max-height: none;
        }
    }
</style>
@endpush

@section('content')
<div class="dlb-shell" id="dynamicLandingBuilder">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Dynamic Landing Builder</li>
                    </ol>
                </div>
                <h4 class="page-title">Dynamic Landing Builder</h4>
            </div>
        </div>
    </div>

    <div class="alert d-none" id="dlbAlert" role="alert"></div>

    <div class="row">
        <div class="col-xl-3 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="mb-0">Pages</h5>
                        <button class="btn btn-sm btn-primary" type="button" id="dlbCreatePageBtn">
                            <i class="mdi mdi-plus"></i>
                        </button>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input class="form-control" id="dlbNewPageName" type="text" placeholder="Campaign page">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input class="form-control" id="dlbNewPageSlug" type="text" placeholder="campaign-page">
                    </div>

                    <div class="dlb-page-list d-grid gap-2" id="dlbPages"></div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="mb-0">Component Library</h5>
                        <button class="btn btn-sm btn-light" type="button" id="dlbRefreshCatalogBtn">
                            <i class="mdi mdi-refresh"></i>
                        </button>
                    </div>
                    <div class="mb-2">
                        <input class="form-control" id="dlbCatalogSearch" type="search" placeholder="Search components">
                    </div>
                    <div class="mb-3">
                        <select class="form-control" id="dlbCatalogCategory">
                            <option value="">All categories</option>
                        </select>
                    </div>
                    <div class="dlb-catalog-list d-grid gap-2" id="dlbCatalog"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-5 col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between dlb-toolbar mb-3">
                        <div>
                            <h5 class="mb-1" id="dlbPageTitle">Select a page</h5>
                            <div class="dlb-muted" id="dlbPageMeta">Create or choose a draft to begin.</div>
                        </div>
                        <div class="d-flex flex-wrap dlb-toolbar">
                            <button class="btn btn-sm btn-light" type="button" id="dlbPreviewBtn" disabled>
                                <i class="mdi mdi-eye-outline"></i> Preview
                            </button>
                            <button class="btn btn-sm btn-success" type="button" id="dlbPublishBtn" disabled>
                                <i class="mdi mdi-cloud-upload-outline"></i> Publish
                            </button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Page Name</label>
                            <input class="form-control" id="dlbPageName" type="text" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Slug</label>
                            <input class="form-control" id="dlbPageSlug" type="text" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SEO Title</label>
                            <input class="form-control" id="dlbSeoTitle" type="text" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SEO Description</label>
                            <input class="form-control" id="dlbSeoDescription" type="text" disabled>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center justify-content-between dlb-toolbar mb-4">
                        <span class="dlb-save-status" id="dlbPageSaveStatus" data-state="saved">
                            <i class="mdi mdi-check-circle-outline"></i>
                            <span>Page saved</span>
                        </span>
                        <button class="btn btn-primary" type="button" id="dlbSavePageBtn" disabled>
                            <i class="mdi mdi-content-save-outline"></i> Save Page
                        </button>
                    </div>

                    <div class="d-flex flex-wrap align-items-center justify-content-between dlb-toolbar mb-3">
                        <div>
                            <h5 class="mb-0">Canvas</h5>
                            <span class="dlb-muted" id="dlbComponentCount">0 items</span>
                        </div>
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-primary dlb-viewport-btn" type="button" data-viewport="desktop">
                                <i class="mdi mdi-monitor"></i>
                            </button>
                            <button class="btn btn-light dlb-viewport-btn" type="button" data-viewport="tablet">
                                <i class="mdi mdi-tablet"></i>
                            </button>
                            <button class="btn btn-light dlb-viewport-btn" type="button" data-viewport="mobile">
                                <i class="mdi mdi-cellphone"></i>
                            </button>
                        </div>
                    </div>
                    <div class="dlb-canvas-shell">
                        <div class="dlb-canvas" id="dlbCanvas" data-viewport="desktop">
                            <div class="dlb-component-list d-grid gap-2" id="dlbPageComponents">
                                <div class="dlb-empty">No page selected.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3">Theme Tokens</h5>
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <label class="form-label">Primary</label>
                            <input class="form-control" id="dlbThemePrimary" type="text" placeholder="#0f766e" disabled>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="form-label">Secondary</label>
                            <input class="form-control" id="dlbThemeSecondary" type="text" placeholder="#f97316" disabled>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="form-label">Background</label>
                            <input class="form-control" id="dlbThemeBackground" type="text" placeholder="#ffffff" disabled>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="form-label">Surface</label>
                            <input class="form-control" id="dlbThemeSurface" type="text" placeholder="#f8fafc" disabled>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="form-label">Text</label>
                            <input class="form-control" id="dlbThemeText" type="text" placeholder="#111827" disabled>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label class="form-label">Muted Text</label>
                            <input class="form-control" id="dlbThemeMutedText" type="text" placeholder="#6b7280" disabled>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">Edit Component</h5>
                            <div class="dlb-muted" id="dlbSelectedComponentName">No component selected</div>
                        </div>
                        <div class="d-flex align-items-center dlb-toolbar">
                            <button class="btn btn-sm btn-light" type="button" id="dlbUndoBtn" disabled>
                                <i class="mdi mdi-undo"></i>
                            </button>
                            <button class="btn btn-sm btn-light" type="button" id="dlbRedoBtn" disabled>
                                <i class="mdi mdi-redo"></i>
                            </button>
                            <button class="btn btn-sm btn-primary" type="button" id="dlbSaveComponentBtn" disabled>
                                <i class="mdi mdi-content-save-outline"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <span class="dlb-save-status" id="dlbComponentSaveStatus" data-state="saved">
                            <i class="mdi mdi-check-circle-outline"></i>
                            <span>Select a component</span>
                        </span>
                    </div>

                    <div class="btn-group btn-group-sm dlb-editor-mode mb-3" role="group">
                        <button class="btn btn-primary" type="button" id="dlbSchemaModeBtn">Fields</button>
                        <button class="btn btn-light" type="button" id="dlbRawModeBtn">JSON</button>
                    </div>

                    <div id="dlbSchemaEditor">
                        <div class="dlb-empty">Select a component to edit its properties.</div>
                    </div>

                    <textarea class="form-control dlb-config-textarea" id="dlbComponentConfig" spellcheck="false" disabled>{}</textarea>
                    <label class="form-label mt-3">Schema Reference</label>
                    <pre class="dlb-schema-box mb-0" id="dlbComponentSchema">Select a component to inspect its schema.</pre>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="mb-0">Advanced</h5>
                        <button class="btn btn-sm btn-light" type="button" id="dlbRefreshSectionsBtn">
                            <i class="mdi mdi-refresh"></i>
                        </button>
                    </div>

                    <div class="row">
                        <div class="col-sm-7 mb-2">
                            <input class="form-control" id="dlbSectionName" type="text" placeholder="Section name">
                        </div>
                        <div class="col-sm-5 mb-2">
                            <input class="form-control" id="dlbSectionCategory" type="text" placeholder="Category">
                        </div>
                    </div>
                    <div class="d-flex flex-wrap dlb-toolbar mb-3">
                        <button class="btn btn-sm btn-primary" type="button" id="dlbSaveSectionBtn" disabled>
                            <i class="mdi mdi-content-save-plus-outline"></i> Save Selected
                        </button>
                        <button class="btn btn-sm btn-light" type="button" id="dlbExportComponentBtn" disabled>
                            <i class="mdi mdi-export"></i> Export Selected
                        </button>
                        <button class="btn btn-sm btn-light" type="button" id="dlbExportPageBtn" disabled>
                            <i class="mdi mdi-code-json"></i> Export Page
                        </button>
                    </div>

                    <label class="form-label">Import JSON</label>
                    <textarea class="form-control dlb-config-textarea" id="dlbImportJson" rows="5" placeholder='{"components":[...]}'></textarea>
                    <div class="d-flex justify-content-end mt-2 mb-3">
                        <button class="btn btn-sm btn-primary" type="button" id="dlbImportBtn" disabled>
                            <i class="mdi mdi-import"></i> Import
                        </button>
                    </div>

                    <label class="form-label">Saved Sections</label>
                    <div class="dlb-catalog-list d-grid gap-2" id="dlbSavedSections">
                        <div class="dlb-empty">No saved sections loaded.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
(function () {
    const routes = {
        catalog: @json(route('admin.dynamic_landing_components.index')),
        savedSections: @json(route('admin.dynamic_landing_saved_sections.index')),
        savedSectionsStore: @json(route('admin.dynamic_landing_saved_sections.store')),
        savedSectionsBase: @json(url('admin/dynamic-landing-saved-sections')),
        pages: @json(route('admin.dynamic_landing_pages.index')),
        pageStore: @json(route('admin.dynamic_landing_pages.store')),
        pageBase: @json(url('admin/dynamic-landing-pages')),
        productOptions: @json(route('admin.dynamic_landing_products.options'))
    };
    const initialPageId = @json(request('page'));

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const state = {
        pages: [],
        catalog: [],
        productOptions: [],
        savedSections: [],
        selectedPage: null,
        selectedComponent: null,
        editorMode: 'schema',
        fieldErrors: {},
        draftComponentId: null,
        draftComponentConfig: null,
        catalogSearch: '',
        catalogCategory: '',
        viewport: 'desktop',
        dirtyFields: new Set(),
        history: [],
        future: [],
        autosaveTimer: null,
        autosaveDelay: 900,
        isAutosaving: false,
        isRestoringHistory: false,
        knownPageUpdatedAt: null,
        knownComponentUpdatedAt: null,
        conflictWarningShown: false,
        pageStatusOverride: null,
        componentStatusOverride: null,
        initialPageId
    };

    const el = {
        alert: document.getElementById('dlbAlert'),
        pages: document.getElementById('dlbPages'),
        catalog: document.getElementById('dlbCatalog'),
        catalogSearch: document.getElementById('dlbCatalogSearch'),
        catalogCategory: document.getElementById('dlbCatalogCategory'),
        canvas: document.getElementById('dlbCanvas'),
        pageComponents: document.getElementById('dlbPageComponents'),
        componentCount: document.getElementById('dlbComponentCount'),
        newPageName: document.getElementById('dlbNewPageName'),
        newPageSlug: document.getElementById('dlbNewPageSlug'),
        createPageBtn: document.getElementById('dlbCreatePageBtn'),
        refreshCatalogBtn: document.getElementById('dlbRefreshCatalogBtn'),
        pageTitle: document.getElementById('dlbPageTitle'),
        pageMeta: document.getElementById('dlbPageMeta'),
        pageName: document.getElementById('dlbPageName'),
        pageSlug: document.getElementById('dlbPageSlug'),
        seoTitle: document.getElementById('dlbSeoTitle'),
        seoDescription: document.getElementById('dlbSeoDescription'),
        savePageBtn: document.getElementById('dlbSavePageBtn'),
        previewBtn: document.getElementById('dlbPreviewBtn'),
        publishBtn: document.getElementById('dlbPublishBtn'),
        themePrimary: document.getElementById('dlbThemePrimary'),
        themeSecondary: document.getElementById('dlbThemeSecondary'),
        themeBackground: document.getElementById('dlbThemeBackground'),
        themeSurface: document.getElementById('dlbThemeSurface'),
        themeText: document.getElementById('dlbThemeText'),
        themeMutedText: document.getElementById('dlbThemeMutedText'),
        selectedComponentName: document.getElementById('dlbSelectedComponentName'),
        schemaEditor: document.getElementById('dlbSchemaEditor'),
        schemaModeBtn: document.getElementById('dlbSchemaModeBtn'),
        rawModeBtn: document.getElementById('dlbRawModeBtn'),
        componentConfig: document.getElementById('dlbComponentConfig'),
        componentSchema: document.getElementById('dlbComponentSchema'),
        saveComponentBtn: document.getElementById('dlbSaveComponentBtn'),
        pageSaveStatus: document.getElementById('dlbPageSaveStatus'),
        componentSaveStatus: document.getElementById('dlbComponentSaveStatus'),
        undoBtn: document.getElementById('dlbUndoBtn'),
        redoBtn: document.getElementById('dlbRedoBtn'),
        refreshSectionsBtn: document.getElementById('dlbRefreshSectionsBtn'),
        sectionName: document.getElementById('dlbSectionName'),
        sectionCategory: document.getElementById('dlbSectionCategory'),
        saveSectionBtn: document.getElementById('dlbSaveSectionBtn'),
        exportComponentBtn: document.getElementById('dlbExportComponentBtn'),
        exportPageBtn: document.getElementById('dlbExportPageBtn'),
        importJson: document.getElementById('dlbImportJson'),
        importBtn: document.getElementById('dlbImportBtn'),
        savedSections: document.getElementById('dlbSavedSections')
    };

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            })[char];
        });
    }

    function slugify(value) {
        return String(value ?? '')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    function setAlert(message, type = 'success') {
        el.alert.className = `alert alert-${type}`;
        el.alert.textContent = message;
        window.clearTimeout(setAlert.timer);
        setAlert.timer = window.setTimeout(() => el.alert.classList.add('d-none'), 5000);
    }

    function setBusy(button, busy) {
        if (!button) {
            return;
        }

        button.disabled = busy;
        button.dataset.busy = busy ? '1' : '0';
    }

    function setStatus(target, status, message, icon = null) {
        const icons = {
            saved: 'mdi-check-circle-outline',
            dirty: 'mdi-circle-edit-outline',
            saving: 'mdi-loading mdi-spin',
            error: 'mdi-alert-circle-outline'
        };

        target.dataset.state = status;
        target.innerHTML = `<i class="mdi ${icon || icons[status] || icons.saved}"></i><span>${escapeHtml(message)}</span>`;
    }

    function renderReliabilityState() {
        el.undoBtn.disabled = !state.selectedComponent || state.history.length < 2 || state.isAutosaving;
        el.redoBtn.disabled = !state.selectedComponent || !state.future.length || state.isAutosaving;

        if (!state.selectedPage) {
            setStatus(el.pageSaveStatus, 'saved', 'No page selected');
        } else if (state.pageStatusOverride) {
            setStatus(el.pageSaveStatus, state.pageStatusOverride.status, state.pageStatusOverride.message);
        } else if (state.dirtyFields.has('page')) {
            setStatus(el.pageSaveStatus, 'dirty', 'Page has unsaved changes');
        } else {
            setStatus(el.pageSaveStatus, 'saved', 'Page saved');
        }

        if (!state.selectedComponent) {
            setStatus(el.componentSaveStatus, 'saved', 'Select a component');
        } else if (state.isAutosaving) {
            setStatus(el.componentSaveStatus, 'saving', 'Autosaving');
        } else if (state.componentStatusOverride) {
            setStatus(el.componentSaveStatus, state.componentStatusOverride.status, state.componentStatusOverride.message);
        } else if (state.dirtyFields.has(`component:${state.selectedComponent.id}`)) {
            setStatus(el.componentSaveStatus, 'dirty', 'Unsaved component changes');
        } else {
            setStatus(el.componentSaveStatus, 'saved', 'Component saved');
        }
    }

    function stableStringify(value) {
        return JSON.stringify(value ?? {});
    }

    function currentEditorConfig() {
        if (!state.selectedComponent) {
            return {};
        }

        return state.editorMode === 'schema'
            ? buildConfigFromSchemaEditor()
            : JSON.parse(el.componentConfig.value || '{}');
    }

    function rememberComponentHistory() {
        if (!state.selectedComponent || state.isRestoringHistory) {
            return;
        }

        let config;
        try {
            config = currentEditorConfig();
        } catch (error) {
            return;
        }

        const previous = state.history[state.history.length - 1];
        const encoded = stableStringify(config);

        if (previous && stableStringify(previous.config) === encoded) {
            return;
        }

        state.history.push({
            componentId: state.selectedComponent.id,
            config
        });

        if (state.history.length > 25) {
            state.history.shift();
        }

        state.future = [];
        renderReliabilityState();
    }

    function markPageDirty() {
        if (!state.selectedPage) {
            return;
        }

        state.dirtyFields.add('page');
        state.pageStatusOverride = null;
        renderReliabilityState();
    }

    function markComponentDirty() {
        if (!state.selectedComponent || state.isRestoringHistory) {
            return;
        }

        let config;
        try {
            config = currentEditorConfig();
        } catch (error) {
            state.dirtyFields.add(`component:${state.selectedComponent.id}`);
            renderReliabilityState();
            return;
        }

        state.draftComponentId = state.selectedComponent.id;
        state.draftComponentConfig = config;
        state.dirtyFields.add(`component:${state.selectedComponent.id}`);
        state.componentStatusOverride = null;
        rememberComponentHistory();
        renderReliabilityState();
        scheduleComponentAutosave();
    }

    function scheduleComponentAutosave() {
        window.clearTimeout(state.autosaveTimer);

        if (!state.selectedComponent) {
            return;
        }

        state.autosaveTimer = window.setTimeout(() => {
            saveComponent({ autosave: true });
        }, state.autosaveDelay);
    }

    function detectExternalChange(nextPage) {
        if (
            state.knownPageUpdatedAt
            && nextPage.updated_at
            && state.knownPageUpdatedAt !== nextPage.updated_at
            && state.dirtyFields.size > 0
            && !state.conflictWarningShown
        ) {
            state.conflictWarningShown = true;
            setAlert('This page changed on the server while local edits were pending. Review before publishing.', 'warning');
        }

        state.knownPageUpdatedAt = nextPage.updated_at || null;
    }

    function hasUnsavedChanges() {
        return state.dirtyFields.size > 0;
    }

    function selectedComponentDirtyKey() {
        return state.selectedComponent ? `component:${state.selectedComponent.id}` : null;
    }

    function hasSelectedComponentChanges() {
        const key = selectedComponentDirtyKey();

        return Boolean(key && state.dirtyFields.has(key));
    }

    function confirmDiscardChanges() {
        return !hasUnsavedChanges() || window.confirm('Discard unsaved editor changes?');
    }

    async function ensureSelectedComponentSaved(message = 'Continue without saving pending component edits?') {
        if (!hasSelectedComponentChanges()) {
            return true;
        }

        if (window.confirm('Save pending component edits first?')) {
            await saveComponent({ silent: true });

            return !hasSelectedComponentChanges();
        }

        return window.confirm(message);
    }

    async function requestJson(url, options = {}) {
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                ...(options.headers || {})
            },
            credentials: 'same-origin',
            ...options
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const validationMessage = data.errors
                ? Object.values(data.errors).flat().join(' ')
                : null;
            const error = new Error(validationMessage || data.message || 'Request failed.');
            error.errors = data.errors || {};
            throw error;
        }

        return data;
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
        const storedConfig = Number(state.draftComponentId) === Number(component?.id)
            ? state.draftComponentConfig
            : component?.config;

        return mergeDeep(definition?.defaults || {}, storedConfig || {});
    }

    function isHexColor(value) {
        return typeof value === 'string' && /^#[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/.test(value.trim());
    }

    function inputValueFor(path, meta, config) {
        const value = getPath(config, path, meta.default ?? '');

        if (meta.type === 'color' && value && typeof value === 'object') {
            return JSON.stringify(value, null, 2);
        }

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
            const root = input.closest('.dlb-spacing-quad');
            const values = Array.from(root?.querySelectorAll('.dlb-schema-field') || [])
                .sort((a, b) => Number(a.dataset.spacingIndex || 0) - Number(b.dataset.spacingIndex || 0))
                .map((field) => field.value.trim() || '0');

            while (values.length < 4) {
                values.push('0');
            }

            return values.slice(0, 4);
        }

        if (meta.type === 'color' && input.dataset.valueMode === 'json') {
            return JSON.parse(input.value);
        }

        if (meta.type === 'product_selector') {
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

        return input.value.trim() === '' ? null : input.value.trim();
    }

    function renderInput(field, config) {
        const meta = field.meta;
        const type = meta.type || 'text';
        const value = inputValueFor(field.path, meta, config);
        const required = meta.required ? 'required' : '';
        const common = `class="form-control dlb-schema-field" data-field-path="${escapeHtml(field.path)}" data-field-type="${escapeHtml(type)}" ${required}`;

        if (type === 'textarea') {
            return `<textarea ${common} rows="3">${escapeHtml(value)}</textarea>`;
        }

        if (type === 'select') {
            const options = Array.isArray(meta.options) ? meta.options : [];
            return `
                <select ${common}>
                    ${options.map((option) => {
                        const selected = String(option) === String(value) ? 'selected' : '';
                        return `<option value="${escapeHtml(option)}" ${selected}>${escapeHtml(option)}</option>`;
                    }).join('')}
                </select>
            `;
        }

        if (type === 'boolean') {
            return `
                <div class="form-check form-switch">
                    <input class="form-check-input dlb-schema-field" type="checkbox" role="switch" data-field-path="${escapeHtml(field.path)}" data-field-type="${escapeHtml(type)}" ${value ? 'checked' : ''}>
                </div>
            `;
        }

        if (type === 'spacing_quad') {
            const labels = Array.isArray(meta.labels) && meta.labels.length === 4
                ? meta.labels
                : ['Top', 'Right', 'Bottom', 'Left'];
            const values = Array.isArray(value) ? value : ['', '', '', ''];

            return `
                <div class="dlb-spacing-quad">
                    ${labels.map((label, index) => `
                        <label>
                            <span>${escapeHtml(label)}</span>
                            <input class="form-control dlb-schema-field"
                                   type="text"
                                   value="${escapeHtml(values[index] ?? '')}"
                                   placeholder="0 or 24px"
                                   data-field-path="${escapeHtml(field.path)}"
                                   data-field-type="${escapeHtml(type)}"
                                   data-spacing-index="${index}">
                        </label>
                    `).join('')}
                </div>
            `;
        }

        if (type === 'product_selector') {
            const selectedIds = Array.isArray(value) ? value.map((item) => Number(item)).filter((item) => item > 0) : [];
            const multiple = meta.multiple === false ? '' : 'multiple';
            const optionsById = new Map(state.productOptions.map((product) => [Number(product.id), product]));

            selectedIds.forEach((id) => {
                if (!optionsById.has(id)) {
                    optionsById.set(id, { id, label: `Product #${id}` });
                }
            });

            const options = [...optionsById.values()];

            return `
                <select ${common} ${multiple}>
                    ${meta.multiple === false ? '<option value="">Select product</option>' : ''}
                    ${options.map((product) => {
                        const selected = selectedIds.includes(Number(product.id)) ? 'selected' : '';
                        const price = Number(product.price || 0) > 0 ? ` - ${Number(product.price).toFixed(2)}` : '';
                        return `<option value="${escapeHtml(product.id)}" ${selected}>${escapeHtml(product.label || product.name || `Product #${product.id}`)}${escapeHtml(price)}</option>`;
                    }).join('')}
                </select>
                <div class="dlb-muted mt-1">Showing latest products. Type in product search is coming later; use product ID JSON mode if needed.</div>
            `;
        }

        if (type === 'category_selector') {
            return `<input ${common} type="text" value="${escapeHtml(value)}" placeholder="Comma separated IDs">`;
        }

        if (['repeater', 'array', 'media'].includes(type)) {
            return `<textarea ${common} rows="4" placeholder="JSON value">${escapeHtml(value)}</textarea>`;
        }

        if (type === 'color') {
            if (value && String(value).trim().startsWith('{')) {
                return `<textarea ${common} data-value-mode="json" rows="3" placeholder="JSON style token">${escapeHtml(value)}</textarea>`;
            }

            if (isHexColor(value)) {
                return `<input ${common} type="color" value="${escapeHtml(value)}">`;
            }

            return `<input ${common} type="text" value="${escapeHtml(value)}" placeholder="#ffffff or theme token">`;
        }

        if (type === 'number') {
            const min = meta.min !== undefined ? `min="${escapeHtml(meta.min)}"` : '';
            const max = meta.max !== undefined ? `max="${escapeHtml(meta.max)}"` : '';
            return `<input ${common} type="number" value="${escapeHtml(value)}" ${min} ${max}>`;
        }

        if (type === 'datetime') {
            return `<input ${common} type="datetime-local" value="${escapeHtml(value)}">`;
        }

        if (type === 'url') {
            return `<input ${common} type="url" value="${escapeHtml(value)}">`;
        }

        return `<input ${common} type="text" value="${escapeHtml(value)}">`;
    }

    function componentDefinition(key) {
        return state.catalog.find((definition) => definition.key === key) || null;
    }

    function catalogCategories() {
        return [...new Set(state.catalog.map((definition) => definition.category).filter(Boolean))]
            .sort((a, b) => String(a).localeCompare(String(b)));
    }

    function filteredCatalog() {
        const search = state.catalogSearch.trim().toLowerCase();

        return state.catalog.filter((definition) => {
            const matchesCategory = !state.catalogCategory || definition.category === state.catalogCategory;
            const haystack = `${definition.name} ${definition.key} ${definition.category}`.toLowerCase();
            const matchesSearch = !search || haystack.includes(search);

            return matchesCategory && matchesSearch;
        });
    }

    function renderCatalogFilters() {
        const current = state.catalogCategory;
        const categories = catalogCategories();

        if (current && !categories.includes(current)) {
            state.catalogCategory = '';
        }

        el.catalogCategory.innerHTML = '<option value="">All categories</option>'
            + categories.map((category) => {
                const selected = category === state.catalogCategory ? 'selected' : '';
                return `<option value="${escapeHtml(category)}" ${selected}>${escapeHtml(category)}</option>`;
            }).join('');
    }

    function componentSummary(component, definition) {
        const config = resolvedComponentConfig(component, definition);
        const fields = fieldDefinitions(definition)
            .slice(0, 3)
            .map((field) => {
                const value = getPath(config, field.path, null);

                if (value === null || value === undefined || value === '') {
                    return null;
                }

                const display = Array.isArray(value)
                    ? `${value.length} selected`
                    : String(typeof value === 'object' ? JSON.stringify(value) : value);

                return `${escapeHtml(field.meta.label || field.key)}: ${escapeHtml(display.slice(0, 80))}`;
            })
            .filter(Boolean);

        return fields.length ? fields.join('<br>') : 'No configured preview fields.';
    }

    function renderPages() {
        if (!state.pages.length) {
            el.pages.innerHTML = '<div class="dlb-empty">No dynamic pages yet.</div>';
            return;
        }

        el.pages.innerHTML = state.pages.map((page) => {
            const active = state.selectedPage && Number(state.selectedPage.id) === Number(page.id) ? ' active' : '';
            const badge = page.status === 'published' ? 'success' : 'secondary';

            return `
                <div class="dlb-page-item${active}" data-page-id="${page.id}">
                    <div class="d-flex align-items-center justify-content-between">
                        <strong>${escapeHtml(page.name)}</strong>
                        <span class="badge bg-${badge}">${escapeHtml(page.status)}</span>
                    </div>
                    <div class="dlb-muted mt-1">/landing/${escapeHtml(page.slug)} - ${page.components_count ?? 0} components</div>
                </div>
            `;
        }).join('');
    }

    function renderCatalog() {
        const catalog = filteredCatalog();

        renderCatalogFilters();

        if (!state.catalog.length) {
            el.catalog.innerHTML = '<div class="dlb-empty">No components registered.</div>';
            return;
        }

        if (!catalog.length) {
            el.catalog.innerHTML = '<div class="dlb-empty">No components match the current filter.</div>';
            return;
        }

        el.catalog.innerHTML = catalog.map((definition) => `
            <div class="dlb-catalog-item" draggable="true" data-component-key="${escapeHtml(definition.key)}">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <strong>${escapeHtml(definition.name)}</strong>
                        <div class="dlb-muted">${escapeHtml(definition.key)} - ${escapeHtml(definition.category)}</div>
                    </div>
                    <button class="btn btn-sm btn-light dlb-add-component" type="button" data-component-key="${escapeHtml(definition.key)}" ${state.selectedPage ? '' : 'disabled'}>
                        <i class="mdi mdi-plus"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }

    function componentSnapshot(component) {
        const config = Number(state.draftComponentId) === Number(component.id)
            ? state.draftComponentConfig
            : component.config || {};

        return {
            component_key: component.component_key,
            config,
            is_enabled: Boolean(component.is_enabled)
        };
    }

    function selectedComponentSnapshot() {
        if (!state.selectedComponent) {
            return null;
        }

        const config = Number(state.draftComponentId) === Number(state.selectedComponent.id)
            ? state.draftComponentConfig
            : state.selectedComponent.config || {};

        return {
            component_key: state.selectedComponent.component_key,
            config,
            is_enabled: Boolean(state.selectedComponent.is_enabled)
        };
    }

    function pageExportPayload() {
        return {
            type: 'dynamic_landing_page_components',
            version: 1,
            page: state.selectedPage
                ? {
                    name: state.selectedPage.name,
                    slug: state.selectedPage.slug,
                    theme: state.selectedPage.theme || {},
                    seo: state.selectedPage.seo || {}
                }
                : null,
            components: (state.selectedPage?.components || []).map(componentSnapshot)
        };
    }

    function normalizeImportPayload(payload) {
        if (Array.isArray(payload)) {
            return payload;
        }

        if (Array.isArray(payload?.components)) {
            return payload.components;
        }

        if (payload?.component_key) {
            return [payload];
        }

        return [];
    }

    function showExportPayload(payload) {
        el.importJson.value = JSON.stringify(payload, null, 2);
        setAlert('Export JSON is ready in the import box.');
    }

    function renderSavedSections() {
        if (!state.savedSections.length) {
            el.savedSections.innerHTML = '<div class="dlb-empty">No saved sections yet.</div>';
            return;
        }

        el.savedSections.innerHTML = state.savedSections.map((section) => `
            <div class="dlb-catalog-item">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <strong>${escapeHtml(section.name)}</strong>
                        <div class="dlb-muted">${escapeHtml(section.category || 'uncategorized')} - ${section.components_count || 0} components</div>
                    </div>
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-light dlb-apply-section" type="button" data-section-id="${escapeHtml(section.id)}" ${state.selectedPage ? '' : 'disabled'}>
                            <i class="mdi mdi-plus"></i>
                        </button>
                        <button class="btn btn-light text-danger dlb-delete-section" type="button" data-section-id="${escapeHtml(section.id)}">
                            <i class="mdi mdi-trash-can-outline"></i>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function renderPageForm() {
        const page = state.selectedPage;
        const disabled = !page;
        const theme = page?.theme || {};
        const seo = page?.seo || {};

        el.pageTitle.textContent = page ? page.name : 'Select a page';
        el.pageMeta.textContent = page ? `/landing/${page.slug} - ${page.status}` : 'Create or choose a draft to begin.';
        el.pageName.value = page?.name || '';
        el.pageSlug.value = page?.slug || '';
        el.seoTitle.value = seo.title || '';
        el.seoDescription.value = seo.description || '';
        el.themePrimary.value = theme.primary || '';
        el.themeSecondary.value = theme.secondary || '';
        el.themeBackground.value = theme.background || '';
        el.themeSurface.value = theme.surface || '';
        el.themeText.value = theme.text || '';
        el.themeMutedText.value = theme.muted_text || '';

        [
            el.pageName,
            el.pageSlug,
            el.seoTitle,
            el.seoDescription,
            el.themePrimary,
            el.themeSecondary,
            el.themeBackground,
            el.themeSurface,
            el.themeText,
            el.themeMutedText,
            el.savePageBtn,
            el.previewBtn,
            el.publishBtn,
            el.importBtn,
            el.exportPageBtn
        ].forEach((field) => {
            field.disabled = disabled;
        });

        el.exportComponentBtn.disabled = !state.selectedComponent;
        el.saveSectionBtn.disabled = !state.selectedComponent;
    }

    function renderComponents() {
        const components = state.selectedPage?.components || [];
        el.componentCount.textContent = `${components.length} item${components.length === 1 ? '' : 's'}`;
        el.canvas.dataset.viewport = state.viewport;

        if (!state.selectedPage) {
            el.pageComponents.innerHTML = '<div class="dlb-empty">No page selected.</div>';
            return;
        }

        if (!components.length) {
            el.pageComponents.innerHTML = '<div class="dlb-empty">Add a component from the left catalog.</div>';
            return;
        }

        el.pageComponents.innerHTML = components.map((component, index) => {
            const definition = componentDefinition(component.component_key);
            const active = state.selectedComponent && Number(state.selectedComponent.id) === Number(component.id) ? ' active' : '';
            const status = component.is_enabled ? 'Enabled' : 'Disabled';

            return `
                <div class="dlb-component-item${active}" data-component-id="${component.id}" draggable="true">
                    <div class="d-flex flex-wrap align-items-start justify-content-between dlb-toolbar">
                        <div>
                            <strong>${escapeHtml(definition?.name || component.component_key)}</strong>
                            <div class="dlb-muted">${escapeHtml(component.component_key)} - ${status}</div>
                            <div class="dlb-component-summary">${componentSummary(component, definition)}</div>
                        </div>
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-light dlb-move-component" type="button" data-direction="up" data-component-id="${component.id}" ${index === 0 ? 'disabled' : ''}>
                                <i class="mdi mdi-arrow-up"></i>
                            </button>
                            <button class="btn btn-light dlb-move-component" type="button" data-direction="down" data-component-id="${component.id}" ${index === components.length - 1 ? 'disabled' : ''}>
                                <i class="mdi mdi-arrow-down"></i>
                            </button>
                            <button class="btn btn-light dlb-toggle-component" type="button" data-component-id="${component.id}">
                                <i class="mdi ${component.is_enabled ? 'mdi-eye-off-outline' : 'mdi-eye-outline'}"></i>
                            </button>
                            <button class="btn btn-light dlb-duplicate-component" type="button" data-component-id="${component.id}">
                                <i class="mdi mdi-content-copy"></i>
                            </button>
                            <button class="btn btn-light text-danger dlb-delete-component" type="button" data-component-id="${component.id}">
                                <i class="mdi mdi-trash-can-outline"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function renderPropertyEditor(component, definition) {
        if (!component) {
            el.schemaEditor.innerHTML = '<div class="dlb-empty">Select a component to edit its properties.</div>';
            return;
        }

        if (!definition) {
            el.schemaEditor.innerHTML = '<div class="dlb-empty">Component definition is unavailable. Use JSON mode after the catalog loads.</div>';
            return;
        }

        const fields = fieldDefinitions(definition);
        const config = resolvedComponentConfig(component, definition);

        if (!fields.length) {
            el.schemaEditor.innerHTML = '<div class="dlb-empty">This component has no editable schema fields.</div>';
            return;
        }

        const sections = fields.reduce((groups, field) => {
            groups[field.section] = groups[field.section] || [];
            groups[field.section].push(field);
            return groups;
        }, {});

        el.schemaEditor.innerHTML = Object.entries(sections).map(([section, sectionFields]) => `
            <div class="dlb-property-section mb-3">
                <h6 class="text-uppercase text-muted mb-3">${escapeHtml(section.replace('_', ' '))}</h6>
                ${sectionFields.map((field) => {
                    const error = fieldError(field.path);
                    const meta = field.meta;
                    const help = meta.help || meta.description || '';

                    return `
                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <label class="form-label mb-1">${escapeHtml(meta.label || field.key)}</label>
                                <span class="dlb-field-meta">${escapeHtml(meta.type || 'text')}${meta.required ? ' - required' : ''}</span>
                            </div>
                            ${renderInput(field, config)}
                            ${help ? `<div class="dlb-muted mt-1">${escapeHtml(help)}</div>` : ''}
                            ${error ? `<div class="dlb-property-error">${escapeHtml(error)}</div>` : ''}
                        </div>
                    `;
                }).join('')}
            </div>
        `).join('');
    }

    function buildConfigFromSchemaEditor() {
        const component = state.selectedComponent;
        const definition = componentDefinition(component.component_key);

        if (!definition) {
            throw new Error('Component definition is unavailable.');
        }

        const config = {};

        fieldDefinitions(definition).forEach((field) => {
            const input = Array.from(el.schemaEditor.querySelectorAll('.dlb-schema-field'))
                .find((candidate) => candidate.dataset.fieldPath === field.path);
            if (!input) {
                return;
            }

            setPath(config, field.path, parseEditorValue(input, field.meta));
        });

        return config;
    }

    function renderSelectedComponent() {
        const component = state.selectedComponent;
        const definition = component ? componentDefinition(component.component_key) : null;
        const componentConfig = Number(state.draftComponentId) === Number(component?.id)
            ? state.draftComponentConfig
            : component?.config;

        el.selectedComponentName.textContent = component
            ? `${definition?.name || component.component_key} #${component.id}`
            : 'No component selected';
        el.componentConfig.value = component ? JSON.stringify(componentConfig || {}, null, 2) : '{}';
        el.componentSchema.textContent = definition
            ? JSON.stringify({
                schema: definition.schema || {},
                defaults: definition.defaults || {},
                behaviours: definition.behaviours || [],
                data_resolver: definition.data_resolver || null
            }, null, 2)
            : 'Select a component to inspect its schema.';
        el.componentConfig.disabled = !component;
        el.saveComponentBtn.disabled = !component;
        el.schemaModeBtn.disabled = !component;
        el.rawModeBtn.disabled = !component;
        el.schemaEditor.style.display = state.editorMode === 'schema' ? '' : 'none';
        el.componentConfig.style.display = state.editorMode === 'raw' ? '' : 'none';
        el.schemaModeBtn.className = `btn ${state.editorMode === 'schema' ? 'btn-primary' : 'btn-light'}`;
        el.rawModeBtn.className = `btn ${state.editorMode === 'raw' ? 'btn-primary' : 'btn-light'}`;
        renderPropertyEditor(component, definition);
        renderReliabilityState();
    }

    function renderAll() {
        renderPages();
        renderCatalog();
        renderPageForm();
        renderComponents();
        renderSelectedComponent();
        renderSavedSections();
        renderReliabilityState();
    }

    async function loadPages(selectFirst = true) {
        const payload = await requestJson(routes.pages);
        state.pages = payload.data || [];

        if (state.selectedPage) {
            const stillExists = state.pages.find((page) => Number(page.id) === Number(state.selectedPage.id));
            if (!stillExists) {
                state.selectedPage = null;
                state.selectedComponent = null;
                state.draftComponentId = null;
                state.draftComponentConfig = null;
            }
        }

        if (!state.selectedPage && state.initialPageId) {
            const initialPage = state.pages.find((page) => Number(page.id) === Number(state.initialPageId));
            state.initialPageId = null;

            if (initialPage) {
                await selectPage(initialPage.id);
                return;
            }
        }

        if (!state.selectedPage && selectFirst && state.pages.length) {
            await selectPage(state.pages[0].id);
            return;
        }

        renderAll();
    }

    async function loadCatalog() {
        const payload = await requestJson(routes.catalog);
        state.catalog = payload.data || [];
        renderAll();
    }

    async function loadProductOptions(search = '') {
        const url = new URL(routes.productOptions, window.location.origin);

        if (search) {
            url.searchParams.set('q', search);
        }

        const payload = await requestJson(url.toString());
        state.productOptions = payload.data || [];
        renderSelectedComponent();
    }

    async function loadSavedSections() {
        const payload = await requestJson(routes.savedSections);
        state.savedSections = payload.data || [];
        renderSavedSections();
    }

    async function selectPage(pageId, options = {}) {
        if (!options.force && !confirmDiscardChanges()) {
            return;
        }

        window.clearTimeout(state.autosaveTimer);
        const payload = await requestJson(`${routes.pageBase}/${pageId}`);
        detectExternalChange(payload.data);
        state.selectedPage = payload.data;
        state.selectedComponent = null;
        state.fieldErrors = {};
        state.draftComponentId = null;
        state.draftComponentConfig = null;
        state.dirtyFields.clear();
        state.history = [];
        state.future = [];
        state.knownComponentUpdatedAt = null;
        state.conflictWarningShown = false;
        state.pageStatusOverride = null;
        state.componentStatusOverride = null;
        renderAll();
    }

    function selectedPageComponent(componentId) {
        return (state.selectedPage?.components || [])
            .find((component) => Number(component.id) === Number(componentId)) || null;
    }

    function cleanObjectValues(values) {
        return Object.fromEntries(
            Object.entries(values)
                .filter(([, value]) => String(value ?? '').trim() !== '')
        );
    }

    async function refreshSelectedPage(selectedComponentId = null) {
        if (state.selectedPage) {
            const hadPageDirty = state.dirtyFields.has('page');
            await selectPage(state.selectedPage.id, { force: true });
            if (hadPageDirty) {
                state.dirtyFields.add('page');
            }
            state.selectedComponent = selectedComponentId
                ? selectedPageComponent(selectedComponentId)
                : null;
            state.knownComponentUpdatedAt = state.selectedComponent?.updated_at || null;
            renderAll();
            if (state.selectedComponent) {
                rememberComponentHistory();
            }
        }
        await loadPages(false);
    }

    async function createPage() {
        const name = el.newPageName.value.trim();
        const slug = slugify(el.newPageSlug.value || name);

        if (!name || !slug) {
            setAlert('Name and slug are required.', 'warning');
            return;
        }

        setBusy(el.createPageBtn, true);
        try {
            const payload = await requestJson(routes.pageStore, {
                method: 'POST',
                body: JSON.stringify({ name, slug })
            });
            el.newPageName.value = '';
            el.newPageSlug.value = '';
            state.selectedPage = payload.data;
            state.selectedComponent = null;
            await loadPages(false);
            setAlert('Page created.');
        } catch (error) {
            setAlert(error.message, 'danger');
        } finally {
            setBusy(el.createPageBtn, false);
        }
    }

    async function savePage() {
        if (!state.selectedPage) {
            return;
        }

        if (!await ensureSelectedComponentSaved('Save page and discard pending component edits?')) {
            return;
        }

        setBusy(el.savePageBtn, true);
        try {
            const payload = await requestJson(`${routes.pageBase}/${state.selectedPage.id}`, {
                method: 'PATCH',
                body: JSON.stringify({
                    name: el.pageName.value.trim(),
                    slug: slugify(el.pageSlug.value),
                    seo: cleanObjectValues({
                        title: el.seoTitle.value.trim(),
                        description: el.seoDescription.value.trim()
                    }),
                    theme: cleanObjectValues({
                        primary: el.themePrimary.value.trim(),
                        secondary: el.themeSecondary.value.trim(),
                        background: el.themeBackground.value.trim(),
                        surface: el.themeSurface.value.trim(),
                        text: el.themeText.value.trim(),
                        muted_text: el.themeMutedText.value.trim()
                    })
                })
            });
            state.selectedPage = payload.data;
            state.selectedComponent = null;
            state.dirtyFields.delete('page');
            state.knownPageUpdatedAt = payload.data.updated_at || null;
            state.pageStatusOverride = { status: 'saved', message: 'Page saved' };
            await loadPages(false);
            renderReliabilityState();
            setAlert('Page saved.');
        } catch (error) {
            state.pageStatusOverride = { status: 'error', message: 'Page save failed' };
            renderReliabilityState();
            setAlert(error.message, 'danger');
        } finally {
            setBusy(el.savePageBtn, false);
        }
    }

    async function addComponent(componentKey) {
        if (!state.selectedPage) {
            setAlert('Select or create a page before adding components.', 'warning');
            return;
        }

        if (!await ensureSelectedComponentSaved('Add a component and discard pending component edits?')) {
            return;
        }

        const definition = componentDefinition(componentKey);
        try {
            const payload = await requestJson(`${routes.pageBase}/${state.selectedPage.id}/components`, {
                method: 'POST',
                body: JSON.stringify({
                    component_key: componentKey,
                    config: definition?.defaults || {}
                })
            });
            await refreshSelectedPage(payload.data?.id || null);
            setAlert('Component added.');
        } catch (error) {
            setAlert(error.message, 'danger');
        }
    }

    async function saveSelectedSection() {
        if (!state.selectedComponent) {
            return;
        }

        const snapshot = selectedComponentSnapshot();
        const name = el.sectionName.value.trim();

        if (!name) {
            setAlert('Section name is required.', 'warning');
            return;
        }

        setBusy(el.saveSectionBtn, true);
        try {
            await requestJson(routes.savedSectionsStore, {
                method: 'POST',
                body: JSON.stringify({
                    name,
                    category: el.sectionCategory.value.trim() || null,
                    components: [snapshot]
                })
            });
            el.sectionName.value = '';
            el.sectionCategory.value = '';
            await loadSavedSections();
            setAlert('Saved section created.');
        } catch (error) {
            setAlert(error.message, 'danger');
        } finally {
            setBusy(el.saveSectionBtn, false);
        }
    }

    async function applySavedSection(sectionId) {
        if (!state.selectedPage) {
            return;
        }

        if (!await ensureSelectedComponentSaved('Apply saved section and discard pending component edits?')) {
            return;
        }

        try {
            const payload = await requestJson(`${routes.pageBase}/${state.selectedPage.id}/saved-sections/${sectionId}/apply`, {
                method: 'POST'
            });
            const created = payload.data || [];
            const lastCreated = created[created.length - 1] || null;
            await refreshSelectedPage(lastCreated?.id || null);
            setAlert('Saved section applied.');
        } catch (error) {
            setAlert(error.message, 'danger');
        }
    }

    async function deleteSavedSection(sectionId) {
        if (!window.confirm('Delete this saved section?')) {
            return;
        }

        try {
            await requestJson(`${routes.savedSectionsBase}/${sectionId}`, {
                method: 'DELETE'
            });
            await loadSavedSections();
            setAlert('Saved section deleted.');
        } catch (error) {
            setAlert(error.message, 'danger');
        }
    }

    async function importComponents() {
        if (!state.selectedPage) {
            return;
        }

        if (!await ensureSelectedComponentSaved('Import components and discard pending component edits?')) {
            return;
        }

        let components;
        try {
            components = normalizeImportPayload(JSON.parse(el.importJson.value || '{}'));
        } catch (error) {
            setAlert('Import JSON is invalid.', 'danger');
            return;
        }

        if (!components.length) {
            setAlert('Import JSON must include at least one component.', 'warning');
            return;
        }

        setBusy(el.importBtn, true);
        try {
            const payload = await requestJson(`${routes.pageBase}/${state.selectedPage.id}/components/import`, {
                method: 'POST',
                body: JSON.stringify({ components })
            });
            const created = payload.data || [];
            const lastCreated = created[created.length - 1] || null;
            await refreshSelectedPage(lastCreated?.id || null);
            setAlert('Components imported.');
        } catch (error) {
            setAlert(error.message, 'danger');
        } finally {
            setBusy(el.importBtn, false);
        }
    }

    async function saveComponent(options = {}) {
        options = options && typeof options === 'object' && 'currentTarget' in options ? {} : options;

        if (!state.selectedPage || !state.selectedComponent) {
            return;
        }

        window.clearTimeout(state.autosaveTimer);

        let config;
        try {
            config = state.editorMode === 'schema'
                ? buildConfigFromSchemaEditor()
                : JSON.parse(el.componentConfig.value || '{}');
        } catch (error) {
            state.dirtyFields.add(`component:${state.selectedComponent.id}`);
            state.componentStatusOverride = {
                status: 'error',
                message: options.autosave ? 'Autosave paused: invalid values' : 'Component save failed'
            };
            renderReliabilityState();
            if (!options.autosave && !options.silent) {
                setAlert(state.editorMode === 'schema'
                    ? 'One or more property values are invalid.'
                    : 'Component config must be valid JSON.', 'danger');
            }
            return;
        }

        state.isAutosaving = Boolean(options.autosave);
        renderReliabilityState();
        setBusy(el.saveComponentBtn, true);
        try {
            const componentId = state.selectedComponent.id;
            await requestJson(`${routes.pageBase}/${state.selectedPage.id}/components/${state.selectedComponent.id}`, {
                method: 'PATCH',
                body: JSON.stringify({ config })
            });
            state.fieldErrors = {};
            state.draftComponentId = null;
            state.draftComponentConfig = null;
            state.dirtyFields.delete(`component:${componentId}`);
            await refreshSelectedPage(componentId);
            state.knownComponentUpdatedAt = state.selectedComponent?.updated_at || null;
            state.componentStatusOverride = { status: 'saved', message: options.autosave ? 'Autosaved' : 'Component saved' };
            renderReliabilityState();
            if (!options.autosave && !options.silent) {
                setAlert('Component saved.');
            }
        } catch (error) {
            state.fieldErrors = error.errors || {};
            state.draftComponentId = state.selectedComponent.id;
            state.draftComponentConfig = config;
            state.dirtyFields.add(`component:${state.selectedComponent.id}`);
            renderSelectedComponent();
            state.componentStatusOverride = { status: 'error', message: options.autosave ? 'Autosave failed' : 'Component save failed' };
            renderReliabilityState();
            if (!options.autosave && !options.silent) {
                setAlert(error.message, 'danger');
            }
        } finally {
            state.isAutosaving = false;
            setBusy(el.saveComponentBtn, false);
            renderReliabilityState();
        }
    }

    async function reorderComponent(componentId, direction) {
        if (!await ensureSelectedComponentSaved('Move component and discard pending component edits?')) {
            return;
        }

        const components = state.selectedPage?.components || [];
        const currentIndex = components.findIndex((component) => Number(component.id) === Number(componentId));
        const nextIndex = direction === 'up' ? currentIndex - 1 : currentIndex + 1;

        if (currentIndex < 0 || nextIndex < 0 || nextIndex >= components.length) {
            return;
        }

        const ordered = [...components];
        [ordered[currentIndex], ordered[nextIndex]] = [ordered[nextIndex], ordered[currentIndex]];

        try {
            await requestJson(`${routes.pageBase}/${state.selectedPage.id}/components/reorder`, {
                method: 'POST',
                body: JSON.stringify({ component_ids: ordered.map((component) => component.id) })
            });
            await refreshSelectedPage(state.selectedComponent?.id || componentId);
        } catch (error) {
            setAlert(error.message, 'danger');
        }
    }

    async function reorderComponentBefore(draggedId, targetId) {
        if (!await ensureSelectedComponentSaved('Reorder components and discard pending component edits?')) {
            return;
        }

        const components = state.selectedPage?.components || [];
        const dragged = components.find((component) => Number(component.id) === Number(draggedId));

        if (!dragged || Number(draggedId) === Number(targetId)) {
            return;
        }

        const ordered = components.filter((component) => Number(component.id) !== Number(draggedId));
        const targetIndex = ordered.findIndex((component) => Number(component.id) === Number(targetId));

        if (targetIndex < 0) {
            return;
        }

        ordered.splice(targetIndex, 0, dragged);

        try {
            await requestJson(`${routes.pageBase}/${state.selectedPage.id}/components/reorder`, {
                method: 'POST',
                body: JSON.stringify({ component_ids: ordered.map((component) => component.id) })
            });
            await refreshSelectedPage(state.selectedComponent?.id || draggedId);
        } catch (error) {
            setAlert(error.message, 'danger');
        }
    }

    async function reorderComponentToEnd(componentId) {
        if (!await ensureSelectedComponentSaved('Reorder components and discard pending component edits?')) {
            return;
        }

        const components = state.selectedPage?.components || [];
        const dragged = components.find((component) => Number(component.id) === Number(componentId));

        if (!dragged || Number(components[components.length - 1]?.id) === Number(componentId)) {
            return;
        }

        const ordered = components
            .filter((component) => Number(component.id) !== Number(componentId))
            .concat(dragged);

        try {
            await requestJson(`${routes.pageBase}/${state.selectedPage.id}/components/reorder`, {
                method: 'POST',
                body: JSON.stringify({ component_ids: ordered.map((component) => component.id) })
            });
            await refreshSelectedPage(state.selectedComponent?.id || componentId);
        } catch (error) {
            setAlert(error.message, 'danger');
        }
    }

    async function duplicateComponent(componentId) {
        if (!await ensureSelectedComponentSaved('Duplicate component and discard pending component edits?')) {
            return;
        }

        try {
            await requestJson(`${routes.pageBase}/${state.selectedPage.id}/components/${componentId}/duplicate`, {
                method: 'POST'
            });
            await refreshSelectedPage();
            setAlert('Component duplicated.');
        } catch (error) {
            setAlert(error.message, 'danger');
        }
    }

    async function toggleComponent(componentId) {
        if (!await ensureSelectedComponentSaved('Change visibility and discard pending component edits?')) {
            return;
        }

        const component = selectedPageComponent(componentId);
        if (!component) {
            return;
        }

        try {
            await requestJson(`${routes.pageBase}/${state.selectedPage.id}/components/${componentId}/visibility`, {
                method: 'PATCH',
                body: JSON.stringify({ is_enabled: !component.is_enabled })
            });
            await refreshSelectedPage(componentId);
        } catch (error) {
            setAlert(error.message, 'danger');
        }
    }

    async function deleteComponent(componentId) {
        if (!await ensureSelectedComponentSaved('Delete component and discard pending component edits?')) {
            return;
        }

        if (!window.confirm('Delete this component?')) {
            return;
        }

        try {
            await requestJson(`${routes.pageBase}/${state.selectedPage.id}/components/${componentId}`, {
                method: 'DELETE'
            });
            await refreshSelectedPage();
            setAlert('Component deleted.');
        } catch (error) {
            setAlert(error.message, 'danger');
        }
    }

    async function publishPage() {
        if (!state.selectedPage) {
            return;
        }

        if (!await ensureSelectedComponentSaved('Publish and discard pending component edits?')) {
            return;
        }

        if (hasUnsavedChanges() && !window.confirm('Publish without saving pending editor changes?')) {
            return;
        }

        setBusy(el.publishBtn, true);
        try {
            await requestJson(`${routes.pageBase}/${state.selectedPage.id}/publish`, {
                method: 'POST'
            });
            await refreshSelectedPage(state.selectedComponent?.id || null);
            setAlert('Page published.');
        } catch (error) {
            setAlert(error.message, 'danger');
        } finally {
            setBusy(el.publishBtn, false);
        }
    }

    function selectComponent(componentId) {
        window.clearTimeout(state.autosaveTimer);
        state.selectedComponent = selectedPageComponent(componentId);
        state.fieldErrors = {};
        state.draftComponentId = null;
        state.draftComponentConfig = null;
        state.history = [];
        state.future = [];
        state.componentStatusOverride = null;
        state.knownComponentUpdatedAt = state.selectedComponent?.updated_at || null;
        renderAll();
        rememberComponentHistory();
    }

    function restoreComponentConfig(config) {
        if (!state.selectedComponent) {
            return;
        }

        state.isRestoringHistory = true;
        state.draftComponentId = state.selectedComponent.id;
        state.draftComponentConfig = config;
        state.dirtyFields.add(`component:${state.selectedComponent.id}`);
        renderSelectedComponent();
        state.isRestoringHistory = false;
        markComponentDirty();
    }

    function undoComponentChange() {
        if (!state.selectedComponent || state.history.length < 2) {
            return;
        }

        const current = state.history.pop();
        state.future.push(current);
        restoreComponentConfig(state.history[state.history.length - 1].config);
    }

    function redoComponentChange() {
        if (!state.selectedComponent || !state.future.length) {
            return;
        }

        const next = state.future.pop();
        state.history.push(next);
        restoreComponentConfig(next.config);
    }

    el.newPageName.addEventListener('input', function () {
        if (!el.newPageSlug.value.trim()) {
            el.newPageSlug.value = slugify(el.newPageName.value);
        }
    });
    el.createPageBtn.addEventListener('click', createPage);
    el.savePageBtn.addEventListener('click', savePage);
    el.saveComponentBtn.addEventListener('click', saveComponent);
    el.undoBtn.addEventListener('click', undoComponentChange);
    el.redoBtn.addEventListener('click', redoComponentChange);
    el.publishBtn.addEventListener('click', publishPage);
    el.previewBtn.addEventListener('click', async function () {
        if (state.selectedPage?.preview_url) {
            if (!await ensureSelectedComponentSaved('Open preview and discard pending component edits?')) {
                return;
            }

            if (hasUnsavedChanges() && !window.confirm('Open preview without saving pending editor changes?')) {
                return;
            }
            window.open(state.selectedPage.preview_url, '_blank', 'noopener');
        }
    });
    el.refreshCatalogBtn.addEventListener('click', loadCatalog);
    el.refreshSectionsBtn.addEventListener('click', loadSavedSections);
    el.saveSectionBtn.addEventListener('click', saveSelectedSection);
    el.exportComponentBtn.addEventListener('click', function () {
        const snapshot = selectedComponentSnapshot();

        if (snapshot) {
            showExportPayload({
                type: 'dynamic_landing_component',
                version: 1,
                components: [snapshot]
            });
        }
    });
    el.exportPageBtn.addEventListener('click', function () {
        if (state.selectedPage) {
            showExportPayload(pageExportPayload());
        }
    });
    el.importBtn.addEventListener('click', importComponents);
    el.catalogSearch.addEventListener('input', function () {
        state.catalogSearch = el.catalogSearch.value;
        renderCatalog();
    });
    el.catalogCategory.addEventListener('change', function () {
        state.catalogCategory = el.catalogCategory.value;
        renderCatalog();
    });

    [
        el.pageName,
        el.pageSlug,
        el.seoTitle,
        el.seoDescription,
        el.themePrimary,
        el.themeSecondary,
        el.themeBackground,
        el.themeSurface,
        el.themeText,
        el.themeMutedText
    ].forEach((input) => {
        input.addEventListener('input', markPageDirty);
    });

    el.schemaEditor.addEventListener('focusin', function (event) {
        if (event.target.closest('.dlb-schema-field')) {
            rememberComponentHistory();
        }
    });

    el.schemaEditor.addEventListener('input', function (event) {
        if (event.target.closest('.dlb-schema-field')) {
            markComponentDirty();
        }
    });

    el.schemaEditor.addEventListener('change', function (event) {
        if (event.target.closest('.dlb-schema-field')) {
            rememberComponentHistory();
            markComponentDirty();
        }
    });

    el.componentConfig.addEventListener('focus', rememberComponentHistory);
    el.componentConfig.addEventListener('input', markComponentDirty);
    el.componentConfig.addEventListener('change', function () {
        rememberComponentHistory();
        markComponentDirty();
    });

    document.querySelectorAll('.dlb-viewport-btn').forEach((button) => {
        button.addEventListener('click', function () {
            state.viewport = button.dataset.viewport || 'desktop';
            document.querySelectorAll('.dlb-viewport-btn').forEach((item) => {
                item.className = `btn ${item.dataset.viewport === state.viewport ? 'btn-primary' : 'btn-light'} dlb-viewport-btn`;
            });
            renderComponents();
        });
    });

    el.pages.addEventListener('click', function (event) {
        const item = event.target.closest('[data-page-id]');
        if (item) {
            selectPage(item.dataset.pageId).catch((error) => setAlert(error.message, 'danger'));
        }
    });

    el.catalog.addEventListener('click', function (event) {
        const button = event.target.closest('.dlb-add-component');
        if (button) {
            addComponent(button.dataset.componentKey);
        }
    });

    el.catalog.addEventListener('dragstart', function (event) {
        const item = event.target.closest('[data-component-key]');
        if (item) {
            event.dataTransfer.effectAllowed = 'copy';
            event.dataTransfer.setData('application/x-dlb-component-key', item.dataset.componentKey);
            event.dataTransfer.setData('text/plain', `component:${item.dataset.componentKey}`);
        }
    });

    el.savedSections.addEventListener('click', function (event) {
        const applyButton = event.target.closest('.dlb-apply-section');
        const deleteButton = event.target.closest('.dlb-delete-section');

        if (applyButton) {
            applySavedSection(applyButton.dataset.sectionId);
            return;
        }

        if (deleteButton) {
            deleteSavedSection(deleteButton.dataset.sectionId);
        }
    });

    el.pageComponents.addEventListener('click', async function (event) {
        const actionButton = event.target.closest('button');
        const item = event.target.closest('[data-component-id]');

        if (actionButton) {
            event.stopPropagation();
            const componentId = actionButton.dataset.componentId;

            if (actionButton.classList.contains('dlb-move-component')) {
                reorderComponent(componentId, actionButton.dataset.direction);
            } else if (actionButton.classList.contains('dlb-toggle-component')) {
                toggleComponent(componentId);
            } else if (actionButton.classList.contains('dlb-duplicate-component')) {
                duplicateComponent(componentId);
            } else if (actionButton.classList.contains('dlb-delete-component')) {
                deleteComponent(componentId);
            }
            return;
        }

        if (item) {
            if (!await ensureSelectedComponentSaved('Select another component and discard pending component edits?')) {
                return;
            }

            selectComponent(item.dataset.componentId);
        }
    });

    el.schemaModeBtn.addEventListener('click', function () {
        state.editorMode = 'schema';
        renderSelectedComponent();
    });

    el.rawModeBtn.addEventListener('click', function () {
        state.editorMode = 'raw';
        renderSelectedComponent();
    });

    el.pageComponents.addEventListener('dragstart', function (event) {
        if (event.target.closest('button')) {
            event.preventDefault();
            return;
        }

        const item = event.target.closest('[data-component-id]');
        if (item) {
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('application/x-dlb-component-id', item.dataset.componentId);
            event.dataTransfer.setData('text/plain', `instance:${item.dataset.componentId}`);
        }
    });

    el.canvas.addEventListener('dragover', function (event) {
        const types = Array.from(event.dataTransfer.types || []);
        if (
            types.includes('application/x-dlb-component-key')
            || types.includes('application/x-dlb-component-id')
        ) {
            event.preventDefault();
        }
    });

    el.canvas.addEventListener('drop', function (event) {
        const item = event.target.closest('[data-component-id]');
        const fallback = event.dataTransfer.getData('text/plain') || '';
        const componentKey = event.dataTransfer.getData('application/x-dlb-component-key')
            || (fallback.startsWith('component:') ? fallback.slice(10) : '');
        const draggedId = event.dataTransfer.getData('application/x-dlb-component-id')
            || (fallback.startsWith('instance:') ? fallback.slice(9) : '');

        if (componentKey) {
            event.preventDefault();
            addComponent(componentKey);
            return;
        }

        if (item && draggedId) {
            event.preventDefault();
            reorderComponentBefore(draggedId, item.dataset.componentId);
            return;
        }

        if (draggedId) {
            event.preventDefault();
            reorderComponentToEnd(draggedId);
        }
    });

    window.addEventListener('beforeunload', function (event) {
        if (!hasUnsavedChanges()) {
            return;
        }

        event.preventDefault();
        event.returnValue = '';
    });

    Promise.all([loadCatalog(), loadPages(), loadSavedSections(), loadProductOptions()])
        .catch((error) => setAlert(error.message, 'danger'));
})();
</script>
@endpush
