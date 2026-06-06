(function ($) {
    'use strict';

    $(document).ready(function () {

        // Génération d'article
        $('#apiapu-generate-btn').on('click', function () {
            var $btn = $(this);
            var $status = $('#apiapu-generate-status');
            var $result = $('#apiapu-generate-result');
            var keywordId = $('#apiapu-keyword-select').val();

            $btn.prop('disabled', true);
            $status.show().find('.apiapu-status-text').text(apiapu_ajax.strings.generating);
            $result.hide();

            $.ajax({
                url: apiapu_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'apiapu_generate_article',
                    nonce: apiapu_ajax.nonce,
                    keyword_id: keywordId
                },
                success: function (response) {
                    $status.hide();
                    $btn.prop('disabled', false);

                    if (response.success) {
                        $result.removeClass('apiapu-error').addClass('apiapu-success').html(
                            '<span class="dashicons dashicons-yes-alt"></span> ' +
                            response.data.message + '<br>' +
                            '<strong>Titre:</strong> ' + response.data.title + '<br>' +
                            '<a href="' + response.data.edit_url + '" class="apiapu-link" target="_blank">Modifier l\'article</a> | ' +
                            '<a href="' + response.data.post_url + '" class="apiapu-link" target="_blank">Voir l\'article</a>'
                        ).show();
                    } else {
                        $result.removeClass('apiapu-success').addClass('apiapu-error').html(
                            '<span class="dashicons dashicons-dismiss"></span> ' +
                            apiapu_ajax.strings.error + ': ' + response.data.message
                        ).show();
                    }
                },
                error: function (xhr, status, error) {
                    $status.hide();
                    $btn.prop('disabled', false);
                    $result.removeClass('apiapu-success').addClass('apiapu-error').html(
                        '<span class="dashicons dashicons-dismiss"></span> ' +
                        'Erreur de connexion: ' + error
                    ).show();
                }
            });
        });

        // Test API
        $('#apiapu-test-api-btn').on('click', function () {
            var $btn = $(this);
            var $result = $('#apiapu-api-test-result');

            $btn.prop('disabled', true);
            $result.removeClass('apiapu-success apiapu-error').text(apiapu_ajax.strings.testing);

            $.ajax({
                url: apiapu_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'apiapu_test_api',
                    nonce: apiapu_ajax.nonce
                },
                success: function (response) {
                    $btn.prop('disabled', false);
                    if (response.success) {
                        $result.addClass('apiapu-success').text(apiapu_ajax.strings.api_ok + ' (Modèle: ' + response.data.model + ')');
                    } else {
                        $result.addClass('apiapu-error').text(apiapu_ajax.strings.api_error + ': ' + response.data.message);
                    }
                },
                error: function () {
                    $btn.prop('disabled', false);
                    $result.addClass('apiapu-error').text('Erreur de connexion');
                }
            });
        });

        // Sauvegarder les paramètres
        $('#apiapu-settings-form').on('submit', function (e) {
            e.preventDefault();

            var $form = $(this);
            var $result = $('#apiapu-save-result');
            var formData = $form.serialize();

            formData += '&action=apiapu_save_settings&nonce=' + apiapu_ajax.nonce;

            // Gérer les checkboxes non cochées
            if (!$('#apiapu-auto-hn').is(':checked')) {
                formData += '&auto_hn=0';
            }
            if (!$('#apiapu-schema-org').is(':checked')) {
                formData += '&schema_org=0';
            }
            if (!$('#apiapu-auto-toc').is(':checked')) {
                formData += '&auto_toc=0';
            }
            if (!$('#apiapu-delete-data').is(':checked')) {
                formData += '&delete_data_on_uninstall=0';
            }

            // Sauvegarder les paramètres cron séparément
            var cronEnabled = $('#apiapu-cron-enabled').is(':checked') ? 1 : 0;
            var cronFrequency = $('#apiapu-cron-frequency').val();

            $.ajax({
                url: apiapu_ajax.ajax_url,
                type: 'POST',
                data: formData,
                success: function (response) {
                    if (response.success) {
                        $result.removeClass('apiapu-error').addClass('apiapu-success').text(apiapu_ajax.strings.saved);

                        // Sauvegarder les paramètres cron
                        $.ajax({
                            url: apiapu_ajax.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'apiapu_save_cron_settings',
                                nonce: apiapu_ajax.nonce,
                                frequency: cronFrequency,
                                enabled: cronEnabled
                            }
                        });
                    } else {
                        $result.removeClass('apiapu-success').addClass('apiapu-error').text(response.data.message);
                    }

                    setTimeout(function () {
                        $result.text('');
                    }, 3000);
                }
            });
        });

        // Afficher/masquer le champ modèle personnalisé
        $('#apiapu-model').on('change', function () {
            if ($(this).val() === 'custom') {
                $('.apiapu-custom-model-row').show();
            } else {
                $('.apiapu-custom-model-row').hide();
            }
        });

        // Exécuter cron maintenant
        $('#apiapu-run-cron-btn').on('click', function () {
            var $btn = $(this);
            $btn.prop('disabled', true).text('Exécution...');

            $.ajax({
                url: apiapu_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'apiapu_run_cron',
                    nonce: apiapu_ajax.nonce
                },
                success: function (response) {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-controls-play"></span> Exécuter cron maintenant');
                    if (response.success) {
                        alert('Cron exécuté avec succès!');
                    } else {
                        alert('Erreur: ' + response.data.message);
                    }
                }
            });
        });

        // Ajouter un mot-clé
        function addKeyword(keyword) {
            if (!keyword.trim()) {
                showKeywordMessage('Veuillez entrer un mot-clé', 'error');
                return;
            }

            $.ajax({
                url: apiapu_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'apiapu_add_keyword',
                    nonce: apiapu_ajax.nonce,
                    keyword: keyword
                },
                success: function (response) {
                    if (response.success) {
                        showKeywordMessage(response.data.message, 'success');
                        location.reload();
                    } else {
                        showKeywordMessage(response.data.message, 'error');
                    }
                }
            });
        }

        $('#apiapu-add-keyword-btn').on('click', function () {
            var keyword = $('#apiapu-new-keyword').val();
            addKeyword(keyword);
        });

        $('#apiapu-new-keyword').on('keypress', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                addKeyword($(this).val());
            }
        });

        // Ajout en masse (Restricted in Free Version)
        $('#apiapu-add-bulk-keywords-btn').on('click', function () {
            alert('La fonctionnalité "Ajout en masse" est disponible uniquement dans la version PRO.\n\nPassez à la version PRO pour générer du contenu en illimité !');
            return false;
        });

        // Supprimer un mot-clé
        $(document).on('click', '.apiapu-delete-keyword-btn', function () {
            if (!confirm(apiapu_ajax.strings.confirm_delete)) return;

            var $btn = $(this);
            var id = $btn.data('id');

            $.ajax({
                url: apiapu_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'apiapu_delete_keyword',
                    nonce: apiapu_ajax.nonce,
                    keyword_id: id
                },
                success: function (response) {
                    if (response.success) {
                        $btn.closest('tr').fadeOut(function () {
                            $(this).remove();
                        });
                    } else {
                        alert(response.data.message);
                    }
                }
            });
        });

        // Générer avec un mot-clé spécifique (depuis la liste des mots-clés)
        $(document).on('click', '.apiapu-generate-keyword-btn', function () {
            var $btn = $(this);
            var id = $btn.data('id');

            if (!confirm('Générer un article avec ce mot-clé ?')) return;

            var originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<span class="dashicons dashicons-update"></span>');

            $.ajax({
                url: apiapu_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'apiapu_generate_article',
                    nonce: apiapu_ajax.nonce,
                    keyword_id: id
                },
                success: function (response) {
                    $btn.prop('disabled', false).html(originalHtml);
                    if (response.success) {
                        alert(apiapu_ajax.strings.success + '\n\nTitre : ' + response.data.title);
                        location.reload();
                    } else {
                        alert(apiapu_ajax.strings.error + ' : ' + response.data.message);
                    }
                },
                error: function (xhr, status, error) {
                    $btn.prop('disabled', false).html(originalHtml);
                    alert('Erreur de connexion : ' + error);
                }
            });
        });

        // Recherche de mots-clés
        $('#apiapu-search-keywords').on('input', function () {
            var search = $(this).val().toLowerCase();
            $('#apiapu-keywords-table tbody tr').each(function () {
                var keyword = $(this).find('.apiapu-keyword-text').text().toLowerCase();
                $(this).toggle(keyword.indexOf(search) !== -1);
            });
        });

        // Filtre par statut
        $('#apiapu-filter-status').on('change', function () {
            var status = $(this).val();
            $('#apiapu-keywords-table tbody tr').each(function () {
                if (!status || $(this).data('status') === status) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            apiapuUpdateBulkState();
        });

        // --- Sélection multiple & suppression groupée des mots-clés ---
        function apiapuUpdateBulkState() {
            var count = $('.apiapu-keyword-cb:checked').length;
            $('#apiapu-bulk-count').text(count);
            $('#apiapu-bulk-delete-btn').prop('disabled', count === 0).css('opacity', count === 0 ? '0.5' : '1');

            var $visible = $('#apiapu-keywords-table tbody tr:visible .apiapu-keyword-cb');
            $('#apiapu-select-all-keywords').prop('checked', $visible.length > 0 && $visible.filter(':checked').length === $visible.length);
        }

        // Tout sélectionner (uniquement les lignes visibles)
        $('#apiapu-select-all-keywords').on('change', function () {
            var checked = $(this).is(':checked');
            $('#apiapu-keywords-table tbody tr:visible .apiapu-keyword-cb').prop('checked', checked);
            apiapuUpdateBulkState();
        });

        // Mise à jour à chaque case cochée
        $(document).on('change', '.apiapu-keyword-cb', apiapuUpdateBulkState);

        // Recalcul après une recherche
        $('#apiapu-search-keywords').on('input', apiapuUpdateBulkState);

        // Suppression groupée (un seul appel AJAX)
        $('#apiapu-bulk-delete-btn').on('click', function () {
            var ids = $('.apiapu-keyword-cb:checked').map(function () { return $(this).val(); }).get();
            if (ids.length === 0) return;
            if (!confirm('Supprimer ' + ids.length + ' mot(s)-clé(s) sélectionné(s) ?')) return;

            var $btn = $(this);
            $btn.prop('disabled', true);

            $.ajax({
                url: apiapu_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'apiapu_delete_keywords',
                    nonce: apiapu_ajax.nonce,
                    keyword_ids: ids
                },
                success: function (response) {
                    if (response.success) {
                        ids.forEach(function (id) {
                            $('#apiapu-keywords-table tbody tr[data-id="' + id + '"]').fadeOut(function () {
                                $(this).remove();
                            });
                        });
                        $('#apiapu-select-all-keywords').prop('checked', false);
                        setTimeout(apiapuUpdateBulkState, 450);
                    } else {
                        alert(response.data.message);
                        $btn.prop('disabled', false);
                    }
                },
                error: function () {
                    alert('Erreur de connexion');
                    $btn.prop('disabled', false);
                }
            });
        });

        function showKeywordMessage(message, type) {
            var $msg = $('#apiapu-keyword-message');
            $msg.removeClass('apiapu-success apiapu-error').addClass('apiapu-' + type).text(message).show();
            setTimeout(function () {
                $msg.fadeOut();
            }, 3000);
        }

        // Sauvegarder le prompt
        $('#apiapu-prompt-form').on('submit', function (e) {
            e.preventDefault();

            var prompt = $('#apiapu-prompt-textarea').val();
            var $result = $('#apiapu-prompt-save-result');

            $.ajax({
                url: apiapu_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'apiapu_save_prompt',
                    nonce: apiapu_ajax.nonce,
                    prompt: prompt
                },
                success: function (response) {
                    if (response.success) {
                        $result.removeClass('apiapu-error').addClass('apiapu-success').text(response.data.message);
                    } else {
                        $result.removeClass('apiapu-success').addClass('apiapu-error').text(response.data.message);
                    }
                    setTimeout(function () {
                        $result.text('');
                    }, 3000);
                }
            });
        });

        // Réinitialiser le prompt
        $('#apiapu-reset-prompt-btn').on('click', function () {
            if (!confirm('Êtes-vous sûr de vouloir réinitialiser le prompt par défaut?')) return;

            $.ajax({
                url: apiapu_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'apiapu_reset_prompt',
                    nonce: apiapu_ajax.nonce
                },
                success: function (response) {
                    if (response.success) {
                        $('#apiapu-prompt-textarea').val(response.data.prompt);
                        updatePromptStats();
                        alert('Prompt réinitialisé!');
                    }
                }
            });
        });

        // Stats du prompt
        function updatePromptStats() {
            var text = $('#apiapu-prompt-textarea').val();
            $('#apiapu-prompt-chars').text(text.length);
            $('#apiapu-prompt-words').text(text.split(/\s+/).filter(function (w) { return w; }).length);
        }

        $('#apiapu-prompt-textarea').on('input', updatePromptStats);

        // Prévisualisation du prompt
        $('#apiapu-preview-prompt-btn').on('click', function () {
            var prompt = $('#apiapu-prompt-textarea').val();
            var keyword = $('#apiapu-test-keyword').val() || 'mot-clé test';
            var preview = prompt.replace(/\{\{KEYWORD\}\}/g, keyword);

            $('#apiapu-prompt-preview-content').text(preview);
            $('#apiapu-prompt-preview').show();
        });

        // Logs
        $('#apiapu-refresh-logs-btn').on('click', function () {
            location.reload();
        });

        $('#apiapu-export-logs-btn').on('click', function () {
            $.ajax({
                url: apiapu_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'apiapu_export_logs',
                    nonce: apiapu_ajax.nonce
                },
                success: function (response) {
                    if (response.success) {
                        var blob = new Blob([response.data.content], { type: 'text/plain' });
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = response.data.filename;
                        link.click();
                    }
                }
            });
        });

        $('#apiapu-clear-logs-btn').on('click', function () {
            if (!confirm('Êtes-vous sûr de vouloir supprimer tous les logs?')) return;

            $.ajax({
                url: apiapu_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'apiapu_clear_logs',
                    nonce: apiapu_ajax.nonce
                },
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        });

        // Filtre des logs
        $('#apiapu-filter-log-type').on('change', function () {
            var type = $(this).val();
            $('.apiapu-log-row').each(function () {
                if (!type || $(this).data('type') === type) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Modal détails
        $(document).on('click', '.apiapu-show-details-btn', function () {
            var details = $(this).data('details');
            try {
                if (typeof details === 'string') {
                    details = JSON.parse(details);
                }
                details = JSON.stringify(details, null, 2);
            } catch (e) { }

            $('#apiapu-details-content').text(details);
            $('#apiapu-details-modal').show();
        });

        $('.apiapu-modal-close, .apiapu-modal').on('click', function (e) {
            if (e.target === this) {
                $('#apiapu-details-modal').hide();
            }
        });

    });

})(jQuery);
