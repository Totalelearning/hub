import './bootstrap';

function initializeUserBulkSelection() {
    document.querySelectorAll('[data-user-bulk-form]').forEach((form) => {
        const checkboxes = Array.from(document.querySelectorAll('[data-user-bulk-checkbox][form="' + form.id + '"]'));
        if (checkboxes.length === 0) {
            return;
        }

        const selectVisibleButton = document.querySelector('[data-user-bulk-select-visible]');
        const selectPresetButton = document.querySelector('[data-user-bulk-select-preset]');
        const clearPresetSelectionButton = document.querySelector('[data-user-bulk-clear-preset-selection]');
        const clearSelectionButton = document.querySelector('[data-user-bulk-clear-selection]');
        const toggleVisibleCheckbox = document.querySelector('[data-user-bulk-toggle-visible]');
        const countLabel = document.querySelector('[data-user-bulk-selection-count]');
        const submitButton = form.querySelector('[data-user-bulk-submit]');
        const actionSelect = form.querySelector('[data-user-bulk-action]');
        const actionSummary = document.querySelector('[data-user-bulk-action-summary]');
        const confirmationHint = document.querySelector('[data-user-bulk-confirmation-hint]');
        const presetSelectionCount = document.querySelector('[data-user-bulk-preset-selection-count]');
        const selectedIdsLabel = document.querySelector('[data-user-bulk-selected-ids]');
        const copyIdsButton = document.querySelector('[data-user-bulk-copy-ids]');
        const copyStatusLabel = document.querySelector('[data-user-bulk-copy-status]');
        const activePreset = form.dataset.userBulkActivePreset || '';

        const actionLabels = {
            resend_verification: 'Resend verification emails to',
            mark_verified: 'Mark verified',
            send_password_reset_link: 'Send password reset links to',
            suspend: 'Suspend',
            restore: 'Restore',
        };

        const updateSelectionState = () => {
            const selectedCheckboxes = checkboxes.filter((checkbox) => checkbox.checked);
            const selectedCount = selectedCheckboxes.length;
            const selectedIds = selectedCheckboxes.map((checkbox) => checkbox.value);
            if (countLabel) {
                countLabel.textContent = selectedCount === 1 ? '1 selected' : `${selectedCount} selected`;
            }

            if (submitButton) {
                submitButton.disabled = selectedCount === 0;
                if (selectedCount === 0) {
                    submitButton.textContent = 'Select users to continue';
                } else {
                    const prefix = actionLabels[actionSelect?.value || 'resend_verification'] || 'Run bulk action for';
                    const noun = selectedCount === 1 ? 'selected user' : 'selected users';
                    submitButton.textContent = `${prefix} ${selectedCount} ${noun}`;
                }
            }

            if (actionSummary) {
                if (selectedCount === 0) {
                    actionSummary.textContent = 'Select an action and at least one visible user.';
                } else {
                    const prefix = actionLabels[actionSelect?.value || 'resend_verification'] || 'Run bulk action for';
                    const noun = selectedCount === 1 ? 'visible user' : 'visible users';
                    actionSummary.textContent = `${prefix} ${selectedCount} ${noun} on this page.`;
                }
            }

            if (presetSelectionCount) {
                const noun = selectedCount === 1 ? 'selected in preset' : 'selected in preset';
                presetSelectionCount.textContent = `${selectedCount} ${noun}`;
            }

            if (confirmationHint) {
                const currentAction = actionSelect?.value;
                confirmationHint.classList.toggle('hidden', !['suspend', 'restore'].includes(currentAction || ''));

                if (currentAction === 'suspend') {
                    confirmationHint.textContent = selectedCount === 0
                        ? 'Bulk suspension requires confirmation before submit.'
                        : `Bulk suspension requires confirmation before submit. ${selectedCount} selected on this page.`;
                } else if (currentAction === 'restore') {
                    confirmationHint.textContent = selectedCount === 0
                        ? 'Bulk restore requires confirmation before submit.'
                        : `Bulk restore requires confirmation before submit. ${selectedCount} selected on this page.`;
                }
            }

            if (selectedIdsLabel) {
                selectedIdsLabel.textContent = selectedIds.length > 0 ? selectedIds.join(', ') : 'None selected.';
            }

            if (copyIdsButton) {
                copyIdsButton.disabled = selectedCount === 0;
                copyIdsButton.classList.toggle('opacity-50', selectedCount === 0);
                copyIdsButton.classList.toggle('cursor-not-allowed', selectedCount === 0);
            }

            if (toggleVisibleCheckbox) {
                const allSelected = checkboxes.every((checkbox) => checkbox.checked);
                const anySelected = checkboxes.some((checkbox) => checkbox.checked);
                toggleVisibleCheckbox.checked = allSelected;
                toggleVisibleCheckbox.indeterminate = anySelected && !allSelected;
            }
        };

        selectVisibleButton?.addEventListener('click', () => {
            checkboxes.forEach((checkbox) => {
                checkbox.checked = true;
            });
            updateSelectionState();
        });

        selectPresetButton?.addEventListener('click', () => {
            checkboxes.forEach((checkbox) => {
                checkbox.checked = true;
            });
            updateSelectionState();
        });

        clearPresetSelectionButton?.addEventListener('click', () => {
            checkboxes.forEach((checkbox) => {
                checkbox.checked = false;
            });
            updateSelectionState();
        });

        clearSelectionButton?.addEventListener('click', () => {
            checkboxes.forEach((checkbox) => {
                checkbox.checked = false;
            });
            updateSelectionState();
        });

        toggleVisibleCheckbox?.addEventListener('change', () => {
            checkboxes.forEach((checkbox) => {
                checkbox.checked = toggleVisibleCheckbox.checked;
            });
            updateSelectionState();
        });

        actionSelect?.addEventListener('change', updateSelectionState);

        form.addEventListener('submit', (event) => {
            const selectedCount = checkboxes.filter((checkbox) => checkbox.checked).length;
            if (selectedCount === 0 || !['suspend', 'restore'].includes(actionSelect?.value || '')) {
                return;
            }

            const noun = selectedCount === 1 ? 'user' : 'users';
            let message = '';

            if (actionSelect?.value === 'suspend') {
                message = activePreset === 'Suspend Inactive 30+'
                    ? `Suspend ${selectedCount} selected ${noun} from Suspend Inactive 30+?`
                    : `Suspend ${selectedCount} selected ${noun}?`;
            }

            if (actionSelect?.value === 'restore') {
                message = activePreset === 'Restore Suspended'
                    ? `Restore ${selectedCount} selected ${noun} from Restore Suspended?`
                    : `Restore ${selectedCount} selected ${noun}?`;
            }

            if (! window.confirm(message)) {
                event.preventDefault();
            }
        });

        copyIdsButton?.addEventListener('click', async () => {
            const selectedIds = checkboxes.filter((checkbox) => checkbox.checked).map((checkbox) => checkbox.value);

            if (selectedIds.length === 0) {
                if (copyStatusLabel) {
                    copyStatusLabel.textContent = 'No selected IDs to copy.';
                }
                return;
            }

            try {
                await navigator.clipboard.writeText(selectedIds.join(','));
                if (copyStatusLabel) {
                    copyStatusLabel.textContent = 'Copied selected IDs.';
                }
            } catch (error) {
                if (copyStatusLabel) {
                    copyStatusLabel.textContent = 'Copy failed.';
                }
            }
        });

        checkboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', updateSelectionState);
        });

        updateSelectionState();
    });
}

initializeUserBulkSelection();

function initializeScormUploadPanels() {
    document.querySelectorAll('[data-scorm-upload-form]').forEach((form) => {
        const input = form.querySelector('[data-scorm-upload-input]');
        const dropzone = form.querySelector('[data-scorm-upload-dropzone]');
        const filename = form.querySelector('[data-scorm-upload-filename]');
        const errorLabel = form.querySelector('[data-scorm-upload-error]');
        const progressLabel = form.querySelector('[data-scorm-upload-progress]');
        const submitButton = form.querySelector('[data-scorm-upload-submit]');
        const maxBytes = Number(form.dataset.scormUploadMaxBytes || 0);

        if (!input || !dropzone || !filename) {
            return;
        }

        const validateFile = (file) => {
            if (!file) {
                return { valid: true, message: '' };
            }

            const lowerName = file.name.toLowerCase();
            if (!lowerName.endsWith('.zip')) {
                return { valid: false, message: 'Only `.zip` SCORM packages are accepted.' };
            }

            if (maxBytes > 0 && file.size > maxBytes) {
                return { valid: false, message: 'The selected file is larger than the 50 MB upload limit.' };
            }

            return { valid: true, message: '' };
        };

        const updateFileState = () => {
            const file = input.files && input.files[0] ? input.files[0] : null;
            const validation = validateFile(file);
            const sizeLabel = file ? ` (${Math.max(1, Math.round(file.size / 1024 / 1024))} MB)` : '';

            filename.textContent = file ? `Selected: ${file.name}${sizeLabel}` : 'No file selected.';

            if (errorLabel) {
                errorLabel.textContent = validation.valid
                    ? 'Choose a valid SCORM `.zip` file under 50 MB.'
                    : validation.message;
                errorLabel.classList.toggle('hidden', validation.valid);
            }

            dropzone.classList.toggle('border-rose-400', !validation.valid);
            dropzone.classList.toggle('bg-rose-50', !validation.valid);
            dropzone.classList.toggle('border-sky-300', validation.valid);
            dropzone.classList.toggle('bg-sky-50', validation.valid);

            if (submitButton) {
                submitButton.disabled = !validation.valid;
                submitButton.classList.toggle('opacity-50', !validation.valid);
                submitButton.classList.toggle('cursor-not-allowed', !validation.valid);
            }

            return validation.valid;
        };

        input.addEventListener('change', updateFileState);

        ['dragenter', 'dragover'].forEach((eventName) => {
            dropzone.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropzone.classList.add('border-sky-500', 'bg-sky-100');
            });
        });

        ['dragleave', 'drop'].forEach((eventName) => {
            dropzone.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropzone.classList.remove('border-sky-500', 'bg-sky-100');
            });
        });

        dropzone.addEventListener('drop', (event) => {
            const files = event.dataTransfer?.files;
            if (!files || files.length === 0) {
                return;
            }

            input.files = files;
            updateFileState();
        });

        form.addEventListener('submit', (event) => {
            if (!updateFileState()) {
                event.preventDefault();
                input.focus();
                return;
            }

            if (progressLabel) {
                progressLabel.classList.remove('hidden');
            }

            dropzone.classList.remove('hover:border-sky-400', 'hover:bg-sky-100');
            dropzone.classList.add('opacity-75', 'cursor-wait');

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Uploading SCORM...';
                submitButton.classList.add('opacity-75', 'cursor-wait');
            }
        });

        updateFileState();
    });
}

initializeScormUploadPanels();

const rankingHealthRoots = document.querySelectorAll('[data-ranking-health-page]');
const rankingProviderLabels = {
    all: 'All providers',
    deterministic: 'Deterministic',
    local_ai: 'Local AI',
    external_ai: 'External AI',
};
const rankingSeverityTriggerLabels = {
    all: 'All triggers',
    ranking_provider_tested: 'Provider Tested',
    ranking_settings_updated: 'Settings Updated',
    ranking_settings_reset: 'Settings Reset',
};

function badgeClasses(success, positiveClass, negativeClass) {
    return success ? positiveClass : negativeClass;
}

function formatTimestamp(value, withSeconds = false) {
    if (!value) {
        return 'n/a';
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return 'n/a';
    }

    const options = {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    };

    if (withSeconds) {
        options.second = '2-digit';
    }

    return new Intl.DateTimeFormat('en-GB', options).format(date).replace(',', '');
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function updateText(root, selector, value) {
    const element = root.querySelector(selector);
    if (element) {
        element.textContent = value;
    }
}

function stampRefreshedAt(root) {
    const now = new Date();
    const label = formatTimestamp(now.toISOString(), true);
    updateText(root, '[data-health-refreshed-at]', `Last updated ${label}`);
}

function currentRankingProviderFilter(root) {
    const select = root.querySelector('[data-ranking-health-provider-filter]');

    return select?.value || 'all';
}

function currentRankingSeverityTriggerFilter(root) {
    const select = root.querySelector('[data-ranking-health-severity-trigger-filter]');

    return select?.value || 'all';
}

function currentRankingExportFrom(root) {
    const input = root.querySelector('[data-ranking-health-export-from]');

    return input?.value || '';
}

function currentRankingExportTo(root) {
    const input = root.querySelector('[data-ranking-health-export-to]');

    return input?.value || '';
}

function rankingHealthEndpoint(root) {
    const endpoint = root.dataset.rankingHealthEndpoint;
    if (!endpoint) {
        return null;
    }

    const url = new URL(endpoint, window.location.origin);
    url.searchParams.set('provider', currentRankingProviderFilter(root));
    url.searchParams.set('trigger', currentRankingSeverityTriggerFilter(root));

    return url.toString();
}

function rankingHealthAuditEndpoint(root) {
    const link = root.querySelector('[data-ranking-health-open-audit]');
    if (!link) {
        return null;
    }

    const url = new URL(link.dataset.auditBaseUrl || link.getAttribute('href'), window.location.origin);
    url.searchParams.set('action', 'ranking_severity_changed');

    const trigger = currentRankingSeverityTriggerFilter(root);
    if (!trigger || trigger === 'all') {
        url.searchParams.delete('q');
    } else {
        url.searchParams.set('q', trigger);
    }

    return url.toString();
}

function rankingHealthProbeExportEndpoint(root) {
    const link = root.querySelector('[data-ranking-health-export-probes]');
    if (!link) {
        return null;
    }

    const url = new URL(link.dataset.exportBaseUrl || link.getAttribute('href'), window.location.origin);
    const provider = currentRankingProviderFilter(root);

    if (!provider || provider === 'all') {
        url.searchParams.delete('ranking_provider');
    } else {
        url.searchParams.set('ranking_provider', provider);
    }

    const exportFrom = currentRankingExportFrom(root);
    const exportTo = currentRankingExportTo(root);

    if (exportFrom) {
        url.searchParams.set('ranking_export_from', exportFrom);
    } else {
        url.searchParams.delete('ranking_export_from');
    }

    if (exportTo) {
        url.searchParams.set('ranking_export_to', exportTo);
    } else {
        url.searchParams.delete('ranking_export_to');
    }

    return url.toString();
}

function rankingHealthSeverityExportEndpoint(root) {
    const link = root.querySelector('[data-ranking-health-export-severity]');
    if (!link) {
        return null;
    }

    const url = new URL(link.dataset.exportBaseUrl || link.getAttribute('href'), window.location.origin);
    const trigger = currentRankingSeverityTriggerFilter(root);

    if (!trigger || trigger === 'all') {
        url.searchParams.delete('ranking_severity_trigger');
    } else {
        url.searchParams.set('ranking_severity_trigger', trigger);
    }

    const exportFrom = currentRankingExportFrom(root);
    const exportTo = currentRankingExportTo(root);

    if (exportFrom) {
        url.searchParams.set('ranking_export_from', exportFrom);
    } else {
        url.searchParams.delete('ranking_export_from');
    }

    if (exportTo) {
        url.searchParams.set('ranking_export_to', exportTo);
    } else {
        url.searchParams.delete('ranking_export_to');
    }

    return url.toString();
}

function rankingHealthIncidentBundleEndpoint(root) {
    const link = root.querySelector('[data-ranking-health-export-json]');
    if (!link) {
        return null;
    }

    const url = new URL(link.dataset.exportBaseUrl || link.getAttribute('href'), window.location.origin);
    const provider = currentRankingProviderFilter(root);
    const trigger = currentRankingSeverityTriggerFilter(root);
    const exportFrom = currentRankingExportFrom(root);
    const exportTo = currentRankingExportTo(root);

    if (!provider || provider === 'all') {
        url.searchParams.delete('ranking_provider');
    } else {
        url.searchParams.set('ranking_provider', provider);
    }

    if (!trigger || trigger === 'all') {
        url.searchParams.delete('ranking_severity_trigger');
    } else {
        url.searchParams.set('ranking_severity_trigger', trigger);
    }

    if (exportFrom) {
        url.searchParams.set('ranking_export_from', exportFrom);
    } else {
        url.searchParams.delete('ranking_export_from');
    }

    if (exportTo) {
        url.searchParams.set('ranking_export_to', exportTo);
    } else {
        url.searchParams.delete('ranking_export_to');
    }

    return url.toString();
}

function syncRankingHealthOpenLinks(root) {
    const endpoint = rankingHealthEndpoint(root);
    if (endpoint) {
        root.querySelectorAll('[data-ranking-health-open-url]').forEach((link) => {
            link.setAttribute('href', endpoint);
        });
        root.querySelectorAll('[data-health-summary-open-url]').forEach((link) => {
            link.setAttribute('href', endpoint);
        });
    }

    const auditEndpoint = rankingHealthAuditEndpoint(root);
    if (auditEndpoint) {
        root.querySelectorAll('[data-ranking-health-open-audit]').forEach((link) => {
            link.setAttribute('href', auditEndpoint);
        });
        root.querySelectorAll('[data-health-summary-open-audit]').forEach((link) => {
            link.setAttribute('href', auditEndpoint);
        });
    }

    const probeExportEndpoint = rankingHealthProbeExportEndpoint(root);
    if (probeExportEndpoint) {
        root.querySelectorAll('[data-ranking-health-export-probes]').forEach((link) => {
            link.setAttribute('href', probeExportEndpoint);
        });
    }

    const severityExportEndpoint = rankingHealthSeverityExportEndpoint(root);
    if (severityExportEndpoint) {
        root.querySelectorAll('[data-ranking-health-export-severity]').forEach((link) => {
            link.setAttribute('href', severityExportEndpoint);
        });
    }

    const incidentBundleEndpoint = rankingHealthIncidentBundleEndpoint(root);
    if (incidentBundleEndpoint) {
        root.querySelectorAll('[data-ranking-health-export-json]').forEach((link) => {
            link.setAttribute('href', incidentBundleEndpoint);
        });
    }
}

async function copyRankingHealthUrl(root) {
    const endpoint = rankingHealthEndpoint(root);
    if (!endpoint) {
        return;
    }

    const status = root.querySelector('[data-ranking-health-copy-status]');

    try {
        if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(endpoint);
            if (status) {
                status.textContent = 'Copied API URL';
            }
            return;
        }
    } catch (_error) {
        // Fall through to the legacy copy path below.
    }

    const input = document.createElement('input');
    input.type = 'text';
    input.value = endpoint;
    input.setAttribute('readonly', 'readonly');
    input.style.position = 'absolute';
    input.style.left = '-9999px';
    document.body.appendChild(input);
    input.select();

    try {
        document.execCommand('copy');
        if (status) {
            status.textContent = 'Copied API URL';
        }
    } catch (_error) {
        if (status) {
            status.textContent = 'Copy failed';
        }
    } finally {
        document.body.removeChild(input);
    }
}

async function copyRankingHealthBundleId(button) {
    const bundleId = button.dataset.rankingHealthCopyBundleId;
    const status = button.parentElement?.querySelector('[data-ranking-health-copy-bundle-status]');

    if (!bundleId) {
        return;
    }

    try {
        if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(bundleId);
            if (status) {
                status.textContent = 'Copied bundle ID';
            }
            return;
        }
    } catch (_error) {
        // Fall through to the legacy copy path below.
    }

    const input = document.createElement('input');
    input.type = 'text';
    input.value = bundleId;
    input.setAttribute('readonly', 'readonly');
    input.style.position = 'absolute';
    input.style.left = '-9999px';
    document.body.appendChild(input);
    input.select();

    try {
        document.execCommand('copy');
        if (status) {
            status.textContent = 'Copied bundle ID';
        }
    } catch (_error) {
        if (status) {
            status.textContent = 'Copy failed';
        }
    } finally {
        document.body.removeChild(input);
    }
}

function updateProviderFilterLabels(root, selectedProvider) {
    const label = rankingProviderLabels[selectedProvider || 'all'] || rankingProviderLabels.all;

    root.querySelectorAll('[data-health-provider-filter-label-wrapper]').forEach((element) => {
        element.textContent = `Viewing ${label}.`;
    });
}

function updateSeverityTriggerFilterLabels(root, selectedTrigger) {
    const label = rankingSeverityTriggerLabels[selectedTrigger || 'all'] || rankingSeverityTriggerLabels.all;

    root.querySelectorAll('[data-health-severity-trigger-filter-label-wrapper]').forEach((element) => {
        element.textContent = `Showing ${label}.`;
    });
}

function updateActiveFilterSummary(root, selectedProvider, selectedTrigger) {
    const providerLabel = rankingProviderLabels[selectedProvider || 'all'] || rankingProviderLabels.all;
    const triggerLabel = rankingSeverityTriggerLabels[selectedTrigger || 'all'] || rankingSeverityTriggerLabels.all;

    root.querySelectorAll('[data-health-active-filter-summary-label]').forEach((element) => {
        element.textContent = `Filters: provider=${providerLabel}, trigger=${triggerLabel}`;
    });
}

function probeHistoryEmptyMessage(selectedProvider) {
    if (!selectedProvider || selectedProvider === 'all') {
        return 'No probe history recorded yet.';
    }

    const label = rankingProviderLabels[selectedProvider] || selectedProvider;

    return `No probe history matches provider ${label}.`;
}

function severityTransitionsEmptyMessage(selectedTrigger) {
    if (!selectedTrigger || selectedTrigger === 'all') {
        return 'No severity transitions recorded yet.';
    }

    const label = rankingSeverityTriggerLabels[selectedTrigger] || selectedTrigger;

    return `No severity transitions match trigger ${label}.`;
}

function updateScopeBadge(root, selectedProvider, selectedTrigger) {
    const filterCount = [
        selectedProvider && selectedProvider !== 'all',
        selectedTrigger && selectedTrigger !== 'all',
    ].filter(Boolean).length;
    const filtered = filterCount > 0;
    const label = filtered ? 'scope: filtered' : 'scope: global';
    const classes = filtered
        ? 'bg-indigo-100 text-indigo-700'
        : 'bg-gray-100 text-gray-700';

    root.querySelectorAll('[data-health-scope-badge]').forEach((element) => {
        element.textContent = label;
        element.className = element.className
            .replace(/\bbg-indigo-100\b|\btext-indigo-700\b|\bbg-gray-100\b|\btext-gray-700\b/g, '')
            .trim();
        element.classList.add(...classes.split(' '));
    });

    root.querySelectorAll('[data-health-filter-count]').forEach((element) => {
        element.textContent = `${filterCount} active filter${filterCount === 1 ? '' : 's'}`;
    });
}

function updateProviderMismatch(root, selectedProvider, activeProvider) {
    const element = root.querySelector('[data-health-provider-mismatch]');
    if (!element) {
        return;
    }

    if (!selectedProvider || selectedProvider === 'all' || selectedProvider === activeProvider) {
        element.classList.add('hidden');
        return;
    }

    const selectedLabel = rankingProviderLabels[selectedProvider] || selectedProvider;
    const activeLabel = rankingProviderLabels[activeProvider] || activeProvider || 'Deterministic';
    element.textContent = `Filtered probe history is showing ${selectedLabel}, while the active ranking provider is ${activeLabel}.`;
    element.classList.remove('hidden');
}

function renderFailureSummary(root, failures) {
    const container = root.querySelector('[data-health-failure-summary]');
    if (!container) {
        return;
    }

    if (!failures || failures.length === 0) {
        container.innerHTML = '<div class="text-sm text-gray-500">No recent failures in this probe window.</div>';
        return;
    }

    const itemClass = root.dataset.rankingHealthPage === 'dashboard'
        ? 'rounded border border-amber-200 bg-amber-50 px-3 py-3 text-sm text-amber-900'
        : 'rounded border border-amber-200 bg-amber-50 px-3 py-3 text-sm text-amber-900';

    container.innerHTML = failures.map((failure) => `
        <div class="${itemClass}">
            <div class="font-semibold">${escapeHtml(failure.label ?? 'Unknown failure')}</div>
            <div class="mt-1 text-xs text-amber-800">count ${escapeHtml(failure.count ?? 0)}; providers ${escapeHtml((failure.providers ?? []).join(', ') || 'n/a')}</div>
            ${(failure.sources ?? []).length > 0 ? `<div class="mt-1 text-xs text-amber-800">sources ${escapeHtml((failure.sources ?? []).join(', '))}</div>` : ''}
            <div class="mt-1 text-xs text-amber-700">${escapeHtml(failure.message ?? 'Unknown failure')}</div>
        </div>
    `).join('');
}

function renderLiveRankingFailures(root, failures) {
    const container = root.querySelector('[data-health-live-failures]');
    if (!container) {
        return;
    }

    if (!failures || failures.length === 0) {
        container.innerHTML = '<div class="text-sm text-gray-500">No recent live ranking failures in this window.</div>';
        return;
    }

    container.innerHTML = failures.map((failure) => `
        <div class="rounded border border-rose-200 bg-rose-50 px-3 py-3 text-sm text-rose-900">
            <div class="flex items-center justify-between gap-3">
                <div class="font-semibold">${escapeHtml(failure.provider ?? 'n/a')}</div>
                <div class="text-xs text-rose-700">${escapeHtml(formatTimestamp(failure.created_at, true))}</div>
            </div>
            <div class="mt-1 text-xs text-rose-800">request ${escapeHtml(failure.request_id || 'n/a')}; latency ${failure.latency_ms != null ? `${escapeHtml(failure.latency_ms)} ms` : 'n/a'}</div>
            <div class="mt-1 text-xs text-rose-700">${escapeHtml(failure.message || 'Unknown runtime failure.')}</div>
            <div class="mt-2">
                <a href="${escapeHtml(buildAiUsageOpsUrl(failure))}" class="rounded border border-rose-300 px-2 py-1 text-xs font-semibold text-rose-800 hover:bg-white">
                    Open Ops
                </a>
            </div>
        </div>
    `).join('');
}

function buildAiUsageOpsUrl(failure) {
    const url = new URL('/app/admin/ai/usages', window.location.origin);

    if (failure?.provider) {
        url.searchParams.set('provider', failure.provider);
    }

    url.searchParams.set('capability', 'feed_ranking');
    url.searchParams.set('success', '0');

    if (failure?.request_id) {
        url.searchParams.set('request_id', failure.request_id);
    }

    url.searchParams.set('limit', '10');

    return `${url.pathname}${url.search}`;
}

function renderSeverityTransitions(root, transitions) {
    const container = root.querySelector('[data-health-severity-transitions]');
    if (!container) {
        return;
    }

    const selectedTrigger = currentRankingSeverityTriggerFilter(root);

    if (!transitions || transitions.length === 0) {
        container.innerHTML = `<div class="text-sm text-gray-500">${escapeHtml(severityTransitionsEmptyMessage(selectedTrigger))}</div>`;
        return;
    }

    container.innerHTML = transitions.map((transition) => `
        <div class="rounded border border-gray-200 bg-gray-50/60 px-4 py-3">
            <div class="flex items-center justify-between gap-3">
                <div class="text-sm font-semibold text-gray-900">
                    ${escapeHtml(transition.before_label ?? transition.before_level ?? 'unknown')}
                    <span class="text-gray-400">-&gt;</span>
                    ${escapeHtml(transition.after_label ?? transition.after_level ?? 'unknown')}
                </div>
                <div class="text-xs text-gray-500">${escapeHtml(formatTimestamp(transition.created_at))}</div>
            </div>
            <div class="mt-1 text-xs text-gray-500">
                trigger ${escapeHtml(transition.trigger ?? 'n/a')} by ${escapeHtml(transition.actor_name ?? 'system')}
            </div>
            ${transition.after_reason ? `<div class="mt-1 text-xs text-gray-500">${escapeHtml(transition.after_reason)}</div>` : ''}
        </div>
    `).join('');
}

function renderSeverityTriggerSummary(root, summary, selectedTrigger) {
    const container = root.querySelector('[data-health-severity-trigger-summary]');
    if (!container) {
        return;
    }

    if (!summary || summary.length === 0) {
        container.innerHTML = '<div class="text-xs text-gray-500">No recent severity-transition counts available.</div>';
        return;
    }

    const allCount = summary.reduce((total, row) => total + Number(row.count ?? 0), 0);
    const rows = [{trigger: 'all', count: allCount}, ...summary];

    container.innerHTML = rows.map((row) => {
        const isSelected = selectedTrigger && selectedTrigger !== 'all' && selectedTrigger === row.trigger;
        const isAllSelected = (!selectedTrigger || selectedTrigger === 'all') && row.trigger === 'all';
        const label = rankingSeverityTriggerLabels[row.trigger] || row.trigger;
        const classes = (isSelected || isAllSelected)
            ? 'border-indigo-300 bg-indigo-50 text-indigo-700'
            : 'border-gray-200 bg-white text-gray-700 hover:border-indigo-200 hover:bg-indigo-50/50';

        return `<button type="button" data-ranking-health-trigger-chip data-trigger="${escapeHtml(row.trigger)}" class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold transition ${classes}">${escapeHtml(label)} ${escapeHtml(row.count ?? 0)}</button>`;
    }).join('');
}

function applySeverityTriggerFilter(root, trigger) {
    const select = root.querySelector('[data-ranking-health-severity-trigger-filter]');
    if (select) {
        select.value = trigger;
    }

    syncRankingHealthUrl(currentRankingProviderFilter(root), trigger, currentRankingExportFrom(root), currentRankingExportTo(root));
    syncRankingHealthOpenLinks(root);
    refreshRankingHealth(root);
}

function clearRankingHealthFilters(root) {
    const providerSelect = root.querySelector('[data-ranking-health-provider-filter]');
    const triggerSelect = root.querySelector('[data-ranking-health-severity-trigger-filter]');

    if (providerSelect) {
        providerSelect.value = 'all';
    }

    if (triggerSelect) {
        triggerSelect.value = 'all';
    }

    syncRankingHealthUrl('all', 'all', currentRankingExportFrom(root), currentRankingExportTo(root));
    syncRankingHealthOpenLinks(root);
    refreshRankingHealth(root);
}

function syncRankingHealthExportDateInputs(root, fromValue, toValue) {
    root.querySelectorAll('[data-ranking-health-export-from]').forEach((input) => {
        input.value = fromValue || '';
    });

    root.querySelectorAll('[data-ranking-health-export-to]').forEach((input) => {
        input.value = toValue || '';
    });
}

function syncRankingHealthUrl(selectedProvider, selectedTrigger, exportFrom = '', exportTo = '') {
    const url = new URL(window.location.href);

    if (!selectedProvider || selectedProvider === 'all') {
        url.searchParams.delete('ranking_provider');
    } else {
        url.searchParams.set('ranking_provider', selectedProvider);
    }

    if (!selectedTrigger || selectedTrigger === 'all') {
        url.searchParams.delete('ranking_severity_trigger');
    } else {
        url.searchParams.set('ranking_severity_trigger', selectedTrigger);
    }

    if (!exportFrom) {
        url.searchParams.delete('ranking_export_from');
    } else {
        url.searchParams.set('ranking_export_from', exportFrom);
    }

    if (!exportTo) {
        url.searchParams.delete('ranking_export_to');
    } else {
        url.searchParams.set('ranking_export_to', exportTo);
    }

    window.history.replaceState({}, '', url);
}

function updateBadge(root, selector, success, positiveText, negativeText, positiveClass, negativeClass) {
    const element = root.querySelector(selector);
    if (!element) {
        return;
    }

    element.textContent = success ? positiveText : negativeText;
    element.className = element.className
        .replace(/\bbg-green-100\b|\btext-green-800\b|\bbg-amber-100\b|\btext-amber-800\b/g, '')
        .trim();
    element.classList.add(...badgeClasses(success, positiveClass, negativeClass).split(' '));
}

function updateSeverityBadge(root, selector, severity) {
    const element = root.querySelector(selector);
    if (!element) {
        return;
    }

    const level = severity?.level ?? 'healthy';
    const classes = level === 'critical'
        ? 'bg-red-100 text-red-800'
        : level === 'degraded'
            ? 'bg-amber-100 text-amber-800'
            : 'bg-green-100 text-green-800';

    element.textContent = severity?.label ?? 'Healthy';
    element.className = element.className
        .replace(/\bbg-green-100\b|\btext-green-800\b|\bbg-amber-100\b|\btext-amber-800\b|\bbg-red-100\b|\btext-red-800\b/g, '')
        .trim();
    element.classList.add(...classes.split(' '));
}

function renderRankingPage(root, payload) {
    const status = payload.provider_status ?? {};
    const lastProbe = payload.last_probe;
    const lastSuccessfulProbe = payload.last_successful_probe;
    const successGap = payload.success_gap;
    const probes = payload.recent_probes ?? [];
    const summary = payload.probe_summary ?? {successes: 0, failures: 0};
    const latency = payload.latency_summary ?? {avg_ms: null, min_ms: null, max_ms: null, trend: 'n/a'};
    const failures = payload.failure_summary ?? [];
    const liveFailures = payload.recent_live_failures ?? [];
    const severityTriggerSummary = payload.severity_trigger_summary ?? [];
    const severityTransitions = payload.recent_severity_transitions ?? [];
    const selectedProvider = payload.selected_provider ?? 'all';
    const selectedTrigger = payload.selected_severity_trigger ?? 'all';
    const activeProvider = payload.provider ?? 'deterministic';
    const severity = payload.severity ?? {level: 'healthy', label: 'Healthy', reason: 'Provider is ready and recent probe health is good.'};

    updateProviderFilterLabels(root, selectedProvider);
    updateSeverityTriggerFilterLabels(root, selectedTrigger);
    updateActiveFilterSummary(root, selectedProvider, selectedTrigger);
    updateScopeBadge(root, selectedProvider, selectedTrigger);
    updateProviderMismatch(root, selectedProvider, activeProvider);
    renderFailureSummary(root, failures);
    renderLiveRankingFailures(root, liveFailures);
    renderSeverityTriggerSummary(root, severityTriggerSummary, selectedTrigger);
    renderSeverityTransitions(root, severityTransitions);
    updateSeverityBadge(root, '[data-health-severity-badge]', severity);
    updateText(root, '[data-health-severity-reason]', severity.reason ?? 'Provider is ready and recent probe health is good.');

    updateBadge(
        root,
        '[data-health-readiness-badge]',
        Boolean(status.active_provider_ready),
        'Ready',
        'Needs attention',
        'bg-green-100 text-green-800',
        'bg-amber-100 text-amber-800',
    );

    updateText(root, '[data-health-history-successes]', `Success ${summary.successes ?? 0}`);
    updateText(root, '[data-health-history-failures]', `Failure ${summary.failures ?? 0}`);
    updateText(
        root,
        '[data-health-latency-summary]',
        `avg ${latency.avg_ms ?? 'n/a'} ms, min ${latency.min_ms ?? 'n/a'} ms, max ${latency.max_ms ?? 'n/a'} ms, trend ${latency.trend ?? 'n/a'}`,
    );

    const lastProbeEmpty = root.querySelector('[data-health-last-probe-empty]');
    const lastProbeContent = root.querySelector('[data-health-last-probe-content]');
    const lastProbeBadge = root.querySelector('[data-health-last-probe-badge]');
    if (lastProbe && lastProbeContent && lastProbeEmpty) {
        lastProbeEmpty.classList.add('hidden');
        lastProbeContent.classList.remove('hidden');
        if (lastProbeBadge) {
            lastProbeBadge.classList.remove('hidden');
        }
        updateBadge(
            root,
            '[data-health-last-probe-badge]',
            Boolean(lastProbe.success),
            'Success',
            'Failure',
            'bg-green-100 text-green-800',
            'bg-amber-100 text-amber-800',
        );
        updateText(root, '[data-health-last-probe-provider]', lastProbe.provider ?? 'n/a');
        updateText(root, '[data-health-last-probe-when]', formatTimestamp(lastProbe.created_at, true));
        updateText(root, '[data-health-last-probe-latency]', lastProbe.latency_ms != null ? `${lastProbe.latency_ms} ms` : 'n/a');
        updateText(root, '[data-health-last-probe-request-id]', lastProbe.request_id || 'n/a');
        updateText(root, '[data-health-last-probe-message]', lastProbe.message || 'n/a');
        updateText(root, '[data-health-last-probe-model]', lastProbe.model || 'n/a');
        const errorType = root.querySelector('[data-health-last-probe-error-type]');
        const errorRow = root.querySelector('[data-health-last-probe-error-row]');
        if (errorType && errorRow) {
            if (lastProbe.error_type) {
                errorType.textContent = lastProbe.error_type;
                errorRow.classList.remove('hidden');
            } else {
                errorRow.classList.add('hidden');
            }
        }
    } else if (lastProbeContent && lastProbeEmpty) {
        lastProbeContent.classList.add('hidden');
        lastProbeEmpty.classList.remove('hidden');
        if (lastProbeBadge) {
            lastProbeBadge.classList.add('hidden');
        }
    }

    const lastSuccessfulProbeEmpty = root.querySelector('[data-health-last-successful-probe-empty]');
    const lastSuccessfulProbeContent = root.querySelector('[data-health-last-successful-probe-content]');
    if (lastSuccessfulProbe && lastSuccessfulProbeContent && lastSuccessfulProbeEmpty) {
        lastSuccessfulProbeEmpty.classList.add('hidden');
        lastSuccessfulProbeContent.classList.remove('hidden');
        updateText(root, '[data-health-last-successful-probe-provider]', lastSuccessfulProbe.provider ?? 'n/a');
        updateText(root, '[data-health-last-successful-probe-when]', formatTimestamp(lastSuccessfulProbe.created_at, true));
        updateText(root, '[data-health-last-successful-probe-latency]', lastSuccessfulProbe.latency_ms != null ? `${lastSuccessfulProbe.latency_ms} ms` : 'n/a');
        updateText(root, '[data-health-last-successful-probe-request-id]', lastSuccessfulProbe.request_id || 'n/a');
        updateText(root, '[data-health-last-successful-probe-message]', lastSuccessfulProbe.message || 'n/a');
        updateText(root, '[data-health-last-successful-probe-model]', lastSuccessfulProbe.model || 'n/a');
        const successGapElement = root.querySelector('[data-health-success-gap]');
        if (successGapElement) {
            successGapElement.textContent = `Last known healthy probe was ${successGap?.label ?? 'n/a'} ago.`;
            successGapElement.classList.remove('hidden');
        }
    } else if (lastSuccessfulProbeContent && lastSuccessfulProbeEmpty) {
        lastSuccessfulProbeContent.classList.add('hidden');
        lastSuccessfulProbeEmpty.classList.remove('hidden');
        const successGapElement = root.querySelector('[data-health-success-gap]');
        if (successGapElement) {
            successGapElement.classList.add('hidden');
        }
    }

    const historyTable = root.querySelector('[data-health-history-table]');
    const historyBody = root.querySelector('[data-health-history-body]');
    const historyEmpty = root.querySelector('[data-health-history-empty]');
    if (historyBody && historyEmpty && historyTable) {
        if (probes.length === 0) {
            historyBody.innerHTML = '';
            historyTable.classList.add('hidden');
            historyEmpty.classList.remove('hidden');
            historyEmpty.textContent = probeHistoryEmptyMessage(selectedProvider);
        } else {
            historyTable.classList.remove('hidden');
            historyEmpty.classList.add('hidden');
            historyBody.innerHTML = probes.map((probe) => `
                <tr>
                    <td class="px-4 py-3 text-gray-600">${escapeHtml(formatTimestamp(probe.created_at, true))}</td>
                    <td class="px-4 py-3 font-medium text-gray-900">${escapeHtml(probe.provider ?? 'n/a')}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${probe.success ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'}">
                            ${probe.success ? 'Success' : 'Failure'}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">${probe.latency_ms != null ? `${escapeHtml(probe.latency_ms)} ms` : 'n/a'}</td>
                    <td class="px-4 py-3 text-gray-600">${escapeHtml(probe.request_id || 'n/a')}</td>
                    <td class="px-4 py-3 text-gray-600">${escapeHtml(probe.message || 'n/a')}</td>
                </tr>
            `).join('');
        }
    }
}

function renderDashboardPage(root, payload) {
    const summary = payload.probe_summary ?? {successes: 0, failures: 0};
    const lastProbe = payload.last_probe;
    const lastSuccessfulProbe = payload.last_successful_probe;
    const successGap = payload.success_gap;
    const status = payload.provider_status ?? {};
    const probes = payload.recent_probes ?? [];
    const latency = payload.latency_summary ?? {avg_ms: null, min_ms: null, max_ms: null, trend: 'n/a'};
    const failures = payload.failure_summary ?? [];
    const liveFailures = payload.recent_live_failures ?? [];
    const severityTriggerSummary = payload.severity_trigger_summary ?? [];
    const severityTransitions = payload.recent_severity_transitions ?? [];
    const selectedProvider = payload.selected_provider ?? 'all';
    const selectedTrigger = payload.selected_severity_trigger ?? 'all';
    const activeProvider = payload.provider ?? 'deterministic';
    const severity = payload.severity ?? {level: 'healthy', label: 'Healthy', reason: 'Provider is ready and recent probe health is good.'};

    updateProviderFilterLabels(root, selectedProvider);
    updateSeverityTriggerFilterLabels(root, selectedTrigger);
    updateActiveFilterSummary(root, selectedProvider, selectedTrigger);
    updateScopeBadge(root, selectedProvider, selectedTrigger);
    updateProviderMismatch(root, selectedProvider, activeProvider);
    renderFailureSummary(root, failures);
    renderLiveRankingFailures(root, liveFailures);
    renderSeverityTriggerSummary(root, severityTriggerSummary, selectedTrigger);
    renderSeverityTransitions(root, severityTransitions);
    updateSeverityBadge(root, '[data-health-dashboard-severity-badge]', severity);
    updateText(root, '[data-health-dashboard-severity-reason]', severity.reason ?? 'Provider is ready and recent probe health is good.');

    updateBadge(
        root,
        '[data-health-dashboard-badge]',
        Boolean(status.active_provider_ready),
        'ready',
        'needs attention',
        'bg-green-100 text-green-800',
        'bg-amber-100 text-amber-800',
    );
    updateText(root, '[data-health-dashboard-provider]', payload.provider ?? 'deterministic');
    updateText(
        root,
        '[data-health-dashboard-summary]',
        `enabled=${payload.enabled ? 'yes' : 'no'}, overrides=${payload.override_count ?? 0}, probes ok=${summary.successes ?? 0}, fail=${summary.failures ?? 0}`,
    );

    const lastProbeLine = root.querySelector('[data-health-dashboard-last-probe]');
    const lastProbeMessage = root.querySelector('[data-health-dashboard-last-message]');
    const lastSuccessfulProbeLine = root.querySelector('[data-health-dashboard-last-successful-probe]');
    const successGapLine = root.querySelector('[data-health-dashboard-success-gap]');
    if (lastProbeLine && lastProbeMessage) {
        if (lastProbe) {
            lastProbeLine.textContent = `last probe ${formatTimestamp(lastProbe.created_at)}: ${lastProbe.success ? 'success' : 'failure'}`;
            lastProbeMessage.textContent = lastProbe.message || 'n/a';
        } else {
            lastProbeLine.textContent = 'No ranking probe recorded yet.';
            lastProbeMessage.textContent = '';
        }
    }
    if (lastSuccessfulProbeLine) {
        if (lastSuccessfulProbe) {
            lastSuccessfulProbeLine.textContent = `last success ${formatTimestamp(lastSuccessfulProbe.created_at)} via ${lastSuccessfulProbe.provider ?? 'n/a'}${lastSuccessfulProbe.latency_ms != null ? ` (${lastSuccessfulProbe.latency_ms} ms)` : ''}`;
            if (successGapLine) {
                successGapLine.textContent = `healthy ${successGap?.label ?? 'n/a'} ago`;
            }
        } else {
            lastSuccessfulProbeLine.textContent = 'No successful ranking probe recorded yet.';
            if (successGapLine) {
                successGapLine.textContent = '';
            }
        }
    }

    updateText(root, '[data-health-dashboard-trend]', `ok ${summary.successes ?? 0} / fail ${summary.failures ?? 0}`);
    updateText(
        root,
        '[data-health-dashboard-latency-summary]',
        `avg ${latency.avg_ms ?? 'n/a'} ms, min ${latency.min_ms ?? 'n/a'} ms, max ${latency.max_ms ?? 'n/a'} ms, trend ${latency.trend ?? 'n/a'}`,
    );
    updateBadge(
        root,
        '[data-health-dashboard-status-badge]',
        Boolean(status.active_provider_ready),
        'Ready',
        'Needs attention',
        'bg-green-100 text-green-800',
        'bg-amber-100 text-amber-800',
    );

    const historyBody = root.querySelector('[data-health-dashboard-history-body]');
    const historyEmpty = root.querySelector('[data-health-dashboard-history-empty]');
    if (historyBody) {
        if (probes.length === 0) {
            historyBody.innerHTML = `<tr><td data-health-dashboard-history-empty colspan="5" class="px-5 py-4 text-gray-500">${escapeHtml(probeHistoryEmptyMessage(selectedProvider))}</td></tr>`;
        } else {
            historyBody.innerHTML = probes.map((probe) => `
                <tr>
                    <td class="px-5 py-3 text-gray-600">${escapeHtml(formatTimestamp(probe.created_at))}</td>
                    <td class="px-5 py-3 text-gray-900">${escapeHtml(probe.provider ?? 'n/a')}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${probe.success ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'}">
                            ${probe.success ? 'success' : 'failure'}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-gray-600">${probe.latency_ms != null ? `${escapeHtml(probe.latency_ms)} ms` : 'n/a'}</td>
                    <td class="px-5 py-3 text-gray-600">${escapeHtml(probe.message || 'n/a')}</td>
                </tr>
            `).join('');
        }
    }
}

async function refreshRankingHealth(root) {
    const endpoint = rankingHealthEndpoint(root);
    if (!endpoint) {
        return;
    }

    try {
        const response = await fetch(endpoint, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            return;
        }

        const payload = (await response.json()).data;
        if (!payload) {
            return;
        }

        if (root.dataset.rankingHealthPage === 'ranking') {
            renderRankingPage(root, payload);
        }

        if (root.dataset.rankingHealthPage === 'dashboard') {
            renderDashboardPage(root, payload);
        }

        stampRefreshedAt(root);
    } catch (_error) {
        // Keep the server-rendered state if refresh fails.
    }
}

rankingHealthRoots.forEach((root) => {
    syncRankingHealthOpenLinks(root);
    refreshRankingHealth(root);
    window.setInterval(() => refreshRankingHealth(root), 30000);

    root.querySelectorAll('[data-ranking-health-provider-filter]').forEach((select) => {
        select.addEventListener('change', () => {
            syncRankingHealthUrl(select.value, currentRankingSeverityTriggerFilter(root), currentRankingExportFrom(root), currentRankingExportTo(root));
            syncRankingHealthOpenLinks(root);
            refreshRankingHealth(root);
        });
    });

    root.querySelectorAll('[data-ranking-health-severity-trigger-filter]').forEach((select) => {
        select.addEventListener('change', () => {
            syncRankingHealthUrl(currentRankingProviderFilter(root), select.value, currentRankingExportFrom(root), currentRankingExportTo(root));
            syncRankingHealthOpenLinks(root);
            refreshRankingHealth(root);
        });
    });

    root.querySelectorAll('[data-ranking-health-export-from]').forEach((input) => {
        input.addEventListener('change', () => {
            syncRankingHealthExportDateInputs(root, input.value, currentRankingExportTo(root));
            syncRankingHealthUrl(currentRankingProviderFilter(root), currentRankingSeverityTriggerFilter(root), input.value, currentRankingExportTo(root));
            syncRankingHealthOpenLinks(root);
        });
    });

    root.querySelectorAll('[data-ranking-health-export-to]').forEach((input) => {
        input.addEventListener('change', () => {
            syncRankingHealthExportDateInputs(root, currentRankingExportFrom(root), input.value);
            syncRankingHealthUrl(currentRankingProviderFilter(root), currentRankingSeverityTriggerFilter(root), currentRankingExportFrom(root), input.value);
            syncRankingHealthOpenLinks(root);
        });
    });

    root.querySelectorAll('[data-ranking-health-refresh]').forEach((button) => {
        button.addEventListener('click', () => refreshRankingHealth(root));
    });

    root.querySelectorAll('[data-ranking-health-copy-url]').forEach((button) => {
        button.addEventListener('click', () => copyRankingHealthUrl(root));
    });

    root.querySelectorAll('[data-ranking-health-copy-bundle-id]').forEach((button) => {
        button.addEventListener('click', () => copyRankingHealthBundleId(button));
    });

    root.querySelectorAll('[data-ranking-health-clear-filters]').forEach((button) => {
        button.addEventListener('click', () => clearRankingHealthFilters(root));
    });

    root.addEventListener('click', (event) => {
        const triggerChip = event.target.closest('[data-ranking-health-trigger-chip]');
        if (!triggerChip) {
            return;
        }

        applySeverityTriggerFilter(root, triggerChip.dataset.trigger || 'all');
    });
});
