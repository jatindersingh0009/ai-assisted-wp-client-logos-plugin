/* Client Logos — edit screen: repeater, media pickers, AJAX save. */
jQuery(function ($) {
	'use strict';

	var $box = $('#cl-metabox');
	if (!$box.length || typeof clAdmin === 'undefined') return;

	var t = clAdmin.i18n;
	var $list = $('#cl-logo-list');
	var tpl = $('#tmpl-cl-logo-row').html();
	var index = $list.children().length;
	var $status = $('#cl-status');
	var $spinner = $('#cl-spinner');

	/* ---------- helpers ---------- */
	function refresh() {
		var n = $list.children().length;
		$('#cl-logo-count').text(n);
		$('#cl-logo-empty').prop('hidden', n > 0);
	}

	function setDirty() {
		$status.removeClass('is-ok is-error').text(t.unsaved);
	}

	function thumbUrl(att) {
		var s = att.sizes || {};
		return (s.medium || s.thumbnail || s.full || att).url;
	}

	function fillRow($row, att) {
		$row.find('.cl-logo-id').val(att.id);
		$row.find('.cl-thumb').html($('<img>', { src: thumbUrl(att), alt: '' }));
		var $alt = $row.find('input[name$="[alt]"]');
		if (!$alt.val()) $alt.val(att.alt || att.title || '');
		$row.removeClass('cl-row--empty');
	}

	function addRow(att) {
		var $row = $(tpl.replace(/__i__/g, index++));
		$list.append($row);
		if (att) fillRow($row, att);
		refresh();
		setDirty();
		return $row;
	}

	function mediaFrame(opts, onSelect) {
		var frame = wp.media({
			title: opts.title,
			button: { text: opts.button },
			library: { type: 'image' },
			multiple: !!opts.multiple
		});
		frame.on('select', function () {
			onSelect(frame.state().get('selection').toJSON());
		});
		frame.open();
	}

	/* ---------- logos repeater ---------- */
	$('#cl-add-logo').on('click', function () {
		var $row = addRow();
		$row.find('.cl-choose').trigger('click');
	});

	$('#cl-bulk-add').on('click', function () {
		mediaFrame({ title: t.chooseLogos, button: t.useMany, multiple: true }, function (items) {
			items.forEach(function (att) { addRow(att); });
		});
	});

	$list.on('click', '.cl-choose', function () {
		var $row = $(this).closest('.cl-row');
		mediaFrame({ title: t.chooseLogo, button: t.use }, function (items) {
			if (items[0]) { fillRow($row, items[0]); setDirty(); }
		});
	});

	$list.on('click', '.cl-remove', function () {
		var $row = $(this).closest('.cl-row');
		if ($row.find('.cl-logo-id').val() && !window.confirm(t.confirmDel)) return;
		$row.slideUp(150, function () { $row.remove(); refresh(); setDirty(); });
	});

	$list.sortable({
		handle: '.cl-handle',
		axis: 'y',
		placeholder: 'cl-row cl-row--placeholder',
		forcePlaceholderSize: true,
		update: setDirty
	});

	/* ---------- background image ---------- */
	$('#cl-bg-choose').on('click', function () {
		mediaFrame({ title: t.chooseBg, button: t.use }, function (items) {
			var att = items[0];
			if (!att) return;
			$('#cl-bg-id').val(att.id);
			$('#cl-bg-preview').html($('<img>', { src: thumbUrl(att), alt: '' }));
			$('#cl-bg-remove').prop('hidden', false);
			setDirty();
		});
	});

	$('#cl-bg-remove').on('click', function () {
		$('#cl-bg-id').val('');
		$('#cl-bg-preview').empty();
		$(this).prop('hidden', true);
		setDirty();
	});

	$box.on('input change', ':input', setDirty);

	// Dim the "wait between moves" setting when autoplay is off.
	$('#cl-slider-autoplay').on('change', function () {
		$('#cl-slider-panel').toggleClass('is-autoplay-off', !this.checked);
	});

	/* ---------- AJAX save ---------- */
	function save() {
		var data = $('#post').find(':input[name^="cl["]').serializeArray();
		data.push({ name: 'action', value: 'cl_save_meta' });
		data.push({ name: 'nonce', value: clAdmin.nonce });
		data.push({ name: 'post_id', value: clAdmin.postId });

		var $btn = $('#cl-save').prop('disabled', true);
		$spinner.addClass('is-active');
		$status.removeClass('is-ok is-error').text(t.saving);

		$.post(clAdmin.ajaxUrl, $.param(data))
			.done(function (res) {
				if (res && res.success) {
					var msg = t.saved + ' ' + res.data.time;
					if (res.data.skipped) msg += ' — ' + t.skipped.replace('%d', res.data.skipped);
					$status.addClass('is-ok').text(msg);
					$list.children('.cl-row--empty').remove();
					refresh();
				} else {
					$status.addClass('is-error').text((res && res.data && res.data.message) || t.error);
				}
			})
			.fail(function (xhr) {
				var m = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message;
				$status.addClass('is-error').text(m || t.error);
			})
			.always(function () {
				$btn.prop('disabled', false);
				$spinner.removeClass('is-active');
			});
	}

	$('#cl-save').on('click', save);

	// Ctrl/Cmd + S inside the box saves via AJAX.
	$box.on('keydown', function (e) {
		if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); save(); }
	});

	/* ---------- shortcode copy ---------- */
	$('#cl-copy').on('click', function () {
		var $b = $(this), code = $b.data('code'), label = $b.text();
		var done = function () { $b.text('Copied'); setTimeout(function () { $b.text(label); }, 1500); };
		if (navigator.clipboard) {
			navigator.clipboard.writeText(code).then(done);
		} else {
			$('.cl-shortcode-input').trigger('select');
			document.execCommand('copy');
			done();
		}
	});

	refresh();
});
