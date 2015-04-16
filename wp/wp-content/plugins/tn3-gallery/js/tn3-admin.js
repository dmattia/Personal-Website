tn3 = {data:[]};
(function ($) {
    var c = this, $_GET, $dialog, $yes, $no, $ok;
    $('document').ready(function () {

	$_GET = getQueryParams(document.location.search);
	$dialog = $('#tn3-dialog');
	$yes = $dialog.find('#tn3-yes');
	$no = $dialog.find('#tn3-no');
	$ok = $dialog.find('#tn3-ok');
	tn3.onTN3Button = onTN3Button;

	$('.tn3-admin-button-add').click(function (e) {
	    $('#tn3-admin-add').slideToggle(300);
	}); 
	$('.tn3-help-list-title').click(function (e) {
	    $(this).toggleClass('tn3-help-list-title-on').parent().find('div').slideToggle('fast');
	}); 

	switch ( $_GET.page ) {
	    case "tn3-settings-general":
		//createSettingsTabs();
		$('input[name$="required"]').each(function (e) {
		    $this = $(this);
		    if (!$this.is(":checked")) $this.parent().find('div').hide();
		    $this.change(function(){
			$(this).parent().find('div').toggle();
		    });
		    //e.preventDefault();
		});
		break;
	    case "tn3-settings-skin":
		initSkinPresets();
		break;
	    case "tn3-settings-transition":
		initTransitionPresets();
		break;
	    case "tn3-albums":
		$('.tn3-album-thumb').click(function() {
		    $thist = $(this);
		    ajaxSelect("image", function(pid) {
			var idpath = tn3.getID(pid[0]).path;
			ipath = tn3.path + "/2" + idpath;
			$thist.html('<img src="' + ipath + '" width="60" height="60" />');
			$thist.parent().find('input[name^="thumb"]').attr("value", idpath);
		    });
		});
		break;
	    default:
		break;
	}
	if ( $_GET.sort != null ) initSorting($_GET.sort);

	if ('undefined' !== typeof list_args) {
	    var ops = list_args['class'].split("_");
	    if (ops[0] == "TN3") initTableActions(ops[1].toLowerCase());
	}
	
    });

    function initTableActions(ttype)
    {
	$('#doaction,#doaction2').click(function (e) {
	    var nact = this.getAttribute("id").substr(2);
	    var act = $('select[name="'+nact+'"]').val();
	    if (act != -1) {
		var ids = [];
		$('input[name="tn3_images"]:checked').each(function() {
		    ids.push($(this).val());
		});
		if (ids.length > 0) eval("do_" + ttype)(act, ids, $('#_wpnonce').val());
	    }

	});
	$('#dosave,#dosave2').click(function (e) {
	    var $table = $(".wp-list-table"),
		pdata = {
		    bulk_type: ttype,
		    tn3_action: "save_data",
		    _wpnonce: $('#_wpnonce').val(),
		    data: {}
		}, name, id;
	    $table.find('input[name="tn3_images"],input[name^="thumb"],input[name^="title"],textarea[name^="description"]').each(function() {
		name = this.getAttribute("name");
		if ( name == "tn3_images" ) {
		    id = this.value;
		    pdata.data[id] = {};
		}
		else pdata.data[ id ][ name.substring(0, name.indexOf("[")) ] = {
		    value: this.value,
		    type: "text"
		};
	    });
	    
	    ajaxit(pdata, function(res) {
		confirmDialog(tn3L18n.success);
	    });

	});
	initSearch(document);
    }
    function initSearch(t, cb)
    {
	$(t).find('#search-submit').click(function (e) {
	    var q = $(t).find("#tn3-search-input").val()
	    if (t == document) do_search(q);
	    else cb(q);
	});
	$(t).find("#tn3-search-input").focus(function(e) {
	    $(this).keydown(function(e) {
		if (e.keyCode == 13) {
		    var q = $(t).find("#tn3-search-input").val()
		    if (t == document) do_search(q);
		    else cb(q);
		};
	    });
	}).blur(function(e) {
	    $(this).unbind("keydown");
	});
    }
    function do_images(act, ids, nonce)
    {
	switch (act) {
	    case 'add':
		selectAndAdd('album', 'images', ids, nonce, true);
		break;
	    case 'arem':
		var postData = {
		    bulk_type: 'images',
		    tn3_action: "arem",
		    aid: $_GET.album,
		    id: ids,
		    _wpnonce: nonce
		};
		ajaxit(postData, function(data) {
		    window.location.reload();
		    //confirmDialog('Operation Successful.');
		});
		break;
	    case 'del':
		confirmAndDelete('images', ids, nonce);
		break;
	    default:

		break;
	}
    }
    function do_albums(act, ids, nonce)
    {
	switch (act) {
	    case 'add':
		selectAndAdd('gallery', 'albums', ids, nonce);
		break;
	    case 'grem':
		var postData = {
		    bulk_type: 'albums',
		    tn3_action: "grem",
		    gid: $_GET.gallery,
		    id: ids,
		    _wpnonce: nonce
		};
		ajaxit(postData, function(data) {
		    window.location.reload();
		    //confirmDialog('Operation Successful.');
		});
		break;
	    case 'del':
		confirmAndDelete('albums', ids, nonce);
		break;
	    default:

		break;
	}
    } 
    function do_galleries(act, ids, nonce)
    {
	switch (act) {
	    case 'del':
		confirmAndDelete('galleries', ids, nonce);
		break;
	    default:

		break;
	}
    } 
    function do_search(q)
    {
	var ex = false, up, uh = window.location.href.split("?"), uq = uh[1].split("&");
	$.each(uq, function(i, v) {
	    up = v.split("=");
	    if (up[0] == "s") {
		if (q == "") uq.splice(i, 1);
		else {
		    up[1] = q;
		    uq[i] = up.join("=");
		}
		ex = true;
		return false;
	    }
	    uq[i] = up.join("=");
	});
	if (!ex && q != "") uq.push("s=" + q);
	uh[1] = uq.join("&");
	window.location.replace(uh.join("?"));

    }
    tn3.deleteImage = function(id)
    {
	var nonce = $('#_wpnonce').val();
	confirmAndDelete('images', [id], nonce);
    }
    function confirmAndDelete(type, ids, nonce)
    {
	confirmDialog(tn3L18n.rusure, function(bool) {
	    if (!bool) return;
	    var postData = {
		bulk_type: type,
		tn3_action: "del",
		id: ids,
		_wpnonce: nonce
	    };
	    ajaxit(postData, function(data) {
		window.location.reload();
		//confirmDialog('Operation Successful.');
	    });
	});
    }
    // postData - data to send with post
    // cb - callback function
    // getData - data to send with GET
    // notJSON - true if data is json
    function ajaxit(postData, cb, getData)
    {
	if (getData == null) {
	    $.blockUI({
		message: tn3L18n.plzwait
	    });
	}
	postData.screen = "post";
	var url = ajaxurl + "?action=tn3_admin";
	for (var i in getData) url += "&" + i + "=" + getData[i];
	$.ajax({
	    type: 'POST',
	    url: url,
	    data: postData,
	    success: function(data, textStatus, jqXHR) {
		try {
		    var json = $.parseJSON(data);
		    if (json.error) confirmDialog(json.error);
		    else cb(json.result);
		} catch(er) {		    
		    cb(data);
		}
	    },
	    error: function (jqXHR, textStatus, errorThrown) {
		confirmDialog("tn3 ajax error: " + textStatus);
	    }
	});
    }
    function confirmDialog(txt, cb, reload)
    {
	if (typeof txt !== "undefined" || txt !== '') {
	    $dialog.find(':header').text(txt);
	}
	$.blockUI({
	    message: $dialog,
	    focusInput: false,
	    fadeIn: 0,
	    onBlock: function() {
		$('.blockUI').css('cursor','auto');
	    }		
	});
	if (cb) {
	    $yes.click(function() { 
		cb(true);
	    }); 
     
	    $no.click(function() { 
		$.unblockUI({fadeOut:0});
		cb(false);
	    });
	    $yes.show();
	    $no.show();
	    $ok.hide();
	} else {
	    $ok.click(function() { 
		$.unblockUI({fadeOut:0});
		if (reload) window.location.reload();
	    });
	    $yes.hide();
	    $no.hide();
	    $ok.show();
	}
	
    }    
    // stype - what to select (images, albums, galleries)
    // type - form that is selecting (images, albums, galleries)
    function selectAndAdd(stype, type, ids, nonce, multi)
    {
	ajaxSelect(stype, function(pid) {
	    var postData = {
		bulk_type: type,
		tn3_action: "add",
		id: ids,
		parent: pid,
		_wpnonce: nonce
	    };
	    ajaxit(postData, function(data) {
		$('select[name="action"]').val(-1);
		$('input[name="tn3_images"]:checked').attr('checked', false);
		confirmDialog(tn3L18n.success, null, true);
	    });
	}, multi);
    }
    // retrieves list table via ajax
    // docType - tpe of documents to retrieve table for
    // cb - callback function, gets selected ids as argument
    // multi - set true if multi selection is needed
    // cont - div container where table should be loaded
    // cbOnSel - if true each selection will trigger callback function
    // getData - object of GET parameters to be sent via ajax request
    // noempty - true if empty albums/gals should be disabled
    function ajaxSelect(docType, cb, multi, $cont, cbOnSel, getData, noempty)
    {
	var postData = {
	    doc_type: docType,
	    no_sel_btns: cbOnSel,
	    tn3_action: 'select'
	};
	if (multi) postData.multi = true;
	if (noempty) postData.noempty = true;
	if (!$cont) {
	    $cont = $('<div id="tn3-block-select" />');
	    $cont.data('ids', []);
	    $('body').append($cont);
	}

	var $prel = $('<img src="' + tn3.pluginPath + 'images/preloader.gif' + '" />');
	$cont.empty();
	$cont.append( $prel );
	ajaxit(postData, function(data) {// data is select table html
	    
	    $data = $(data);
	    $cont.empty();
	    $cont.prepend($data);
	    $cont.find('.wp-list-table').wrap('<div id="tn3-table-select" />');
	    
	    // select table sorting actions
	    $data.find("a").each(function () {
		$(this).click(function(e) {
		    var glink = $(this).attr('href').split('?')[1];
		    if (glink) {
			var params = getQueryParams(glink);
			ajaxSelect(docType, cb, multi, $cont, cbOnSel, params, noempty);
		    }
		    e.preventDefault();
		});
	    });
	    initSearch($cont, function(q) {
		if (getData) getData.s = q;
		else getData = {s:q};
		ajaxSelect(docType, cb, multi, $cont, cbOnSel, getData, noempty);
	    });
	    var ida = $cont.data('ids'), allchecks = [];
	    $data.find('input[name="tn3_images"]').click(function() {
		var val = $(this).val();
		if (multi) {
		    var ap = $.inArray(val, ida);
		    if (ap == -1) ida.push(val);
		    else ida.splice(ap, 1);
		    if (cbOnSel) cb(ida, postData.doc_type);
		} else {
		    $this = $(this);
		    $.each(allchecks, function(i, $v) {if ($this.val() != $v.val()) $v.attr("checked", false);});
		    ida[0] = val;
		    cb([val], postData.doc_type);
		    if (!cbOnSel) $.unblockUI({fadeOut:0});
		}
	    }).each(function(i, v) {
		var $v = $(v);
		var val = $v.val();
		if ($.inArray(val, $cont.data('ids')) != -1) $v.attr("checked", true);
		allchecks.push($v);	
	    });
	    var allcolchecks = [];
	    $data.find('th.column-cb input').click(function() {
		var ch = $(this).is(":checked");
		$.each(allchecks, function(i, $v) {
		    var val = $v.val();
		    var ap = $.inArray(val, ida);
		    if (ch && (ap == -1)) {
			$v.attr("checked", true);
			ida.push(val);
		    } else if (!ch && (ap != -1)) {
			$v.attr("checked", false);
			ida.splice(ap, 1);
		    }
		});
		$.each(allcolchecks, function(j, $k) { if ($(this) != $k) $k.attr("checked", ch); });
		if (cbOnSel) cb(ida, postData.doc_type);
	    }).each(function(i, v) {
		if (!multi) $(this).hide();
		else allcolchecks.push($(v));	
	    });

	    if (!cbOnSel) {
		if (multi) {
		    tn3.blockDialog($cont, $cont.width(), $cont.height() + 40, null, null, function() {cb($cont.data('ids'));});
		} else {
		    tn3.blockDialog($cont, $cont.width(), $cont.height() + 40, null, null);
		}
	    }
 
	    //setTimeout($.unblockUI, 2000)
	    
	    //$.log(arguments);
	}, getData);
    }

    function createSettingsTabs()
    {
	var $wrap = $('.wrap'), $form, formName, title,
	    hash = document.location.hash.substr(1), selHash = 0,
	    $div = $wrap.append('<div id="tn3-tabs"></div>').find(":last");
	    $ul = $div.append('<ul></ul>').find(":last");
	$wrap.find('form').each(function(n){
	    $form = $(this);
	    formName = $form.attr("id").split("-")[1];
	    title = $form.find('h3').remove().text();
	    $ul.append('<li><a href="#'+formName+'">' + title + '</a></li>');
	    if (hash == title.toLowerCase()) selHash = n;
	    $form.appendTo($div).wrap('<div id="'+formName+'"></div>');
	});
	$div.tabs({selected: selHash});
	
    }


    function getQueryParams(qs) {
	qs = qs.split("+").join(" ");
	var params = {};
	var tokens,
	    re = /[?&]?([^=]+)=([^&]*)/g;

	while (tokens = re.exec(qs)) {
	    params[decodeURIComponent(tokens[1])]
		= decodeURIComponent(tokens[2]);
	}

	return params;
    }

    function initSorting(parentID)
    {
	var $sorted = $(".tn3-sorting"),
	    items = [], cur, i, dorders = [];
	// get init values
	$sorted.find(".tn3-sort-item").each(function () {
	    cur = items[ items.push({
		$e: $(this),
		eid: this.getAttribute("id")
	    }) - 1 ];
	    $(this).find("span").each( function() {
		cur[ this.getAttribute("class").substr(14) ] = $(this).text();
	    });
	});
	for (i in items) dorders.push(parseInt(items[i].dorder));
	dorders.sort(function(a,b) {return (a>b)? 1:-1;});
	
	var eid, newa, newp, sorto = {update: function(e, ui) {
	     eid = ui.item.attr("id");
	     newa = $sorted.sortable('toArray');
	     for (i = 0; i < newa.length; i++) if (newa[i] == eid) {
		 newp = i;
		 break;
	     };
	     for (i = 0; i < items.length; i++) if (items[i].eid == eid) {
		 var it = items.splice(i, 1)[0];
		 items.splice(newp, 0, it);
		 break;
	     };
	}};

	$sorted.sortable(sorto).disableSelection();

	$('select[name="presort"]').change(function (e) {
	    if (this.value == -1) return;
	    $sorted.sortable('destroy');
	    sortOn(items, this.value);
	    for (i = items.length - 1; i >= 0; i-- ) {
		items[i].$e.detach().prependTo($sorted);
	    }
	    $sorted.sortable(sorto);
	});
	$('#reverse').click(function (e) {
	    $sorted.sortable('destroy');
	    items.reverse();
	    for (i = items.length - 1; i >= 0; i-- ) {
		items[i].$e.detach().prependTo($sorted);
	    }
	    $sorted.sortable(sorto);
	});
	$('#save_sort').click(function (e) {
	    var pdata = {
		bulk_type: "sort",
		_wpnonce: $('#_wpnonce').val(),
		parentID: parentID,
		data: {}
	    };
	    for (i = 0; i < items.length; i++) pdata.data[items[i].id] = dorders[i];
	    ajaxit(pdata, function(res) {
		confirmDialog(tn3L18n.success);
	    });

	})
    }
    function sortOn( a, field )
    {
	return a.sort(function(v1, v2) {
	    return (v1[field] <= v2[field]) ? -1 : 1;
	});
    }

    // ***************** tn3 object ******************
   tn3.ImageLoader = function(ur, cont, cb, cbarg)
    {
	this.$img = $(new Image());
	cbarg.unshift(this.$img);
	var o = {
	    url:ur,
	    context:cont,
	    callback:cb,
	    args:cbarg
	};
	this.$img.bind("load", o, this.load);
	this.$img.bind("error", o, this.error);
	this.$img.attr('src', ur);
    }
    tn3.ImageLoader.prototype = {
	$img:null,
	load: function(e) {
	    e.data.callback.apply(e.data.context, e.data.args);
	    e.data.args[0].unbind("load").unbind("error");
	},
	error: function(e) {
	    e.data.args.push("image loading error: " + e.data.url);
	    e.data.callback.apply(e.data.context, e.data.args);
	    this.$img.unbind("load").unbind("error");
	    //$.log("image loading error: " + e.data.url);
	},
	cancel: function() {
	    this.$img.unbind("load");
	    this.$img.unbind("load").unbind("error");

	}
    }
    tn3.getImagePath = function(path, size, cache)
    {
	var u = tn3.path + '/' + size + path,
	    s = "?";
	if (size != 1 && size != 2) {
	    u = ajaxurl + '?action=tn3_alt&u=' + u;
	    s = "&";
	}
	if (cache !== undefined) u += s + "rand=" + Math.round(10000*Math.random());
	return u;
    }
    tn3.preloadImage = function(url, $c, f) {
	var $prel = $('<img src="' + tn3.pluginPath + 'images/preloader.gif' + '" />');
	$c.empty();
	$c.append( $prel );
	new tn3.ImageLoader(url, this, function($img) {
	    $c.empty();
	    $img.appendTo($c);
	    if (f) f.call(this, $img);
	}, []);
    }
    tn3.getID = function(id)
    {
	for (var i=0; i < tn3.data.length; i++) if (tn3.data[i].id == id) return tn3.data[i];
    }
    tn3.blockDialog = function($content, w, h, f, args, okf)
    {
	var cntx = this,
	    $c = $('<div />'),
	    $cnav = $('<div id="tn3-dialog-nav"><br /><div id="tn3-cancel">'+tn3L18n.cancel+'</div><input type="submit" class="button-primary" id="tn3-ok" value="'+tn3L18n.save+'"></div>');
		    
	$.blockUI({ 
	    message: $c, 
	    fadeIn: 0,
	    onBlock: function() {
		var $bp = $('.blockPage');
		
		$bp.width(w).height(h)
		   .css("padding", "10px")
		   .css("left", ($(window).width() - w - 20 ) / 2 + "px")
		   .css("top", ($(window).height() - h - 20 ) / 2 + "px")
		   .css("background-color", "#ffffff")
		   .css("border", "2px solid #ddd");
		$bp.append($content);
		$bp.append($cnav);
		$bp.append('<span style="position:absolute;right:-14px;top:-14px;cursor:pointer;"><img src="'+tn3.pluginPath+'images/close.png" /></span>');

		$bp.find('#tn3-cancel,span img').click(function () {if (cropIsOn) stopCrop();$.unblockUI({fadeOut:0})});
		var $okb = $cnav.find('#tn3-ok');
		
		if (okf) $okb.click(function () {okf.call(cntx);});
		else $okb.hide();
		
		if (f) {
		    args.push($bp);
		    f.apply( cntx, args );
		}
		$('.blockUI').css('cursor','auto');
	    }
	}); 
    }
    var form;
    // find form width and height(written in html) and form fields
    tn3.parseForm = function(htm, pref)
    {
	form = {};
	var reg = new RegExp(/\w.*\w/),
	    m = reg.exec(htm)[0].split("x");

	form.w = parseInt(m[0]);
	form.h = parseInt(m[1]);
	form.fields = [];

	$(htm).find('[name^="tn3_' + pref + '_"]').each(function() {
	    form.fields.push( this.getAttribute('name').substr( 5 + pref.length ) );
	});


    }
    tn3.getForm = function(id)
    {
	var dta = tn3.getID(id),
	    $c = $('.blockPage');

	$.each(form.fields, function(i, v) {
	    $c.find('[name="tn3_image_' + v + '"]').each(function(j, k) {
		if ($(k).is(":hidden")) return true;
		if (v == "title" && $.trim(this.value) == "") return true;
		if (dta[v] == undefined && $.trim(this.value) == "") return true;
		if (this.value != dta[v]) {
		    if (form.save == undefined) form.save = {};
		    if (form.save[id] == undefined) form.save[id] = {};
		    try {
			form.save[id][v] = {
			    value: this.value,
			    type: "text"
			}
		    } catch (e) {
			form.save[id][v] = {
			    value: "image",
			    type: "text"
			}
		    }
		}
	    });
	});
    }
    /* -------------------- show image --------------------- */
    var cropIsOn = false, 
	jCrop, 
	cropControlWidth = 120, 
	$cropControlContainer,
	previewImage,
	previewIniW,
	previewIniH,
	previewRatio = 1,
	currentCropSize,
	$cropPreview,
	currentCropID;
    tn3.showImage = function(id)
    {
	$.blockUI({
	    message: tn3L18n.plzwait
	});
	var url = tn3.path + tn3.getID(id).path;
	new tn3.ImageLoader(url, this, function($img) {
	    var img = $img.get(0);
	    previewImage = $img;
	    previewIniW = img.width;
	    previewIniH = img.height;
	    currentCropID = id;
	    previewRatio = 1;

	    var	pad = 20,
		ww = $(window).width() - 3*pad - cropControlWidth,
		wh = $(window).height() - 6*pad,
		contw, conth;


	    if (previewIniW > ww) {
		if (previewIniH > wh) previewRatio = Math.max(ww/previewIniW, wh/previewIniH);
		else previewRatio = ww/previewIniW;
	    } else if (previewIniH > wh) previewRatio = wh/previewIniH;
	    contw = previewIniW*previewRatio + pad + cropControlWidth;
	    conth = previewIniH*previewRatio + 2*pad;
	    
	    tn3.blockDialog( $img, contw, conth, positionImage, [pad], function() {
		     if (cropIsOn) {saveCrop();return;}
		 } );

	}, []);

	if ( $cropControlContainer == undefined ) {
	    $cropControlContainer = $('<div />');
	    for (var i = 0; i < tn3.sizes.length; i++) {
		$cpcont = $('<div class="tn3-crop-thumb"><p><b>' +tn3.sizes[i].name+ '</b><br />' +tn3.sizes[i].w+' x '+tn3.sizes[i].h+ ' px</p></div>');
		$cthumb = $('<div style="overflow:hidden;float:right;" id="tn3-crop-preview-'+tn3.sizes[i].size+'"><img /></div>');
		$cthumb.width(tn3.sizes[i].w).height(tn3.sizes[i].h);
		if (tn3.sizes[i].w > cropControlWidth) {
		    var crat = tn3.sizes[i].w / tn3.sizes[i].h;
		    $cthumb.width(cropControlWidth)
			   .height(cropControlWidth/crat)
			   .css("border", "1px solid #ff0000");
		    tn3.sizes[i].ratio = cropControlWidth / tn3.sizes[i].w;
		} else {
		    tn3.sizes[i].ratio = 1;
		}
		$cthumb.appendTo($cpcont);
		$cpcont.appendTo($cropControlContainer)
		       .click(tn3.sizes[i], doCrop);
	    }
	}
	loadCropImages(id, undefined, true);
	
    }
    function loadCropImages(id, n, cache)
    {
	var cntx = this;
	var loadIt = function(id, i) {
	    var $ic = $cropControlContainer.find('#tn3-crop-preview-'+tn3.sizes[i].size),
		iurl = tn3.getImagePath(tn3.getID(id).path, tn3.sizes[i].size, cache);
	    $ic.attr("src", "");
	    tn3.preloadImage(iurl, $ic, function($img) {

		$img.attr("width", tn3.sizes[i].w * tn3.sizes[i].ratio)
		    .attr("height", tn3.sizes[i].h * tn3.sizes[i].ratio)
		    .attr("style", "cursor:pointer");
	    });
	}

	if (n === undefined) 
	    for (var i = 0; i < tn3.sizes.length; i++) loadIt(id, i);
	else
	    for (var i = 0; i < tn3.sizes.length; i++) if (tn3.sizes[i].size == n) {
		loadIt(id, i);
		break;
	    }

    }
    function doCrop(e)
    {
	if (cropIsOn) {
	    if (currentCropSize == e.data) return;
	    else stopCrop();
	}
	    currentCropSize = e.data;
	    setCropPreview(true);
	    jCrop = $.Jcrop(previewImage, {
		setSelect: getInitCroptSelection(previewIniW, previewIniH),
		minSize: [e.data.w*previewRatio, e.data.h*previewRatio],
		aspectRatio: e.data.w/e.data.h,
		onChange: updateCropPreview,
		onSelect: updateCropPreview
	    });
	    $save_btn = $cropControlContainer.parent().find('#tn3-ok');
	    $save_btn.show();
	    cropIsOn = true;
	
    }
    function stopCrop()
    {
	$cropControlContainer.parent().find('#tn3-ok').hide();
	setCropPreview(false);
	cropIsOn = false;
	jCrop.destroy();
    }
    function saveCrop()
    {
	var dta = getCropCoords(false, true);
	dta.dw = currentCropSize.w;
	dta.dh = currentCropSize.h;
	dta.q = currentCropSize.q;
	pdata = {
	    bulk_type: "images",
	    tn3_action: "make_thumb",
	    _wpnonce: $('#_wpnonce').val(),
	    size: currentCropSize.size,
	    path: tn3.getID(currentCropID).path,
	    data: dta
	}
	ajaxit(pdata, function() {
	    
	    if (currentCropSize.size == 2) {
		var $ic = $('a[href="javascript:tn3.showImage('+currentCropID+')"]').find("img");
		$ic.attr("src", $ic.attr("src") + "?" + Math.random() );
	    }
	    stopCrop();
	    loadCropImages(currentCropID, currentCropSize.size);
	    	    
	}, {});
    }
    function positionImage(pad, $cont)
    {
	previewImage.width(previewIniW*previewRatio)
		    .height(previewIniH*previewRatio);
	var $imgWrap = previewImage.wrap("<div />").parent();
	$imgWrap.width(previewIniW*previewRatio)
	        .height(previewIniH*previewRatio);

	$cropControlContainer.prependTo($cont)
		   .css("position", "absolute")
		   .css("right", pad + "px")
		   .css("width", cropControlWidth + "px");
	$cont.find('#tn3-ok').hide();

    }
    function setCropPreview(show) {
	if (show) {
	    $cropPreview = $('<img src="'+previewImage.attr("src")+'" >');
	    var $preview = $cropControlContainer.find("#tn3-crop-preview-"+currentCropSize.size);
	    $preview.empty();
	    $cropPreview.appendTo($preview);
	    //$.log($preview);
	 } else {
	    loadCropImages(currentCropID, currentCropSize.size, true);

	 }	    
    }
    function updateCropPreview(c) {
	c = getCropCoords(c);

	$cropPreview.css({
	    width: c.w + 'px',
	    height: c.h + 'px',
	    marginLeft: '-' + c.x + 'px',
	    marginTop: '-' + c.y + 'px'
	});
    }
    function getInitCroptSelection(w, h) {
	//var rat = tw/th;
	var tw = currentCropSize.w, 
	    th = currentCropSize.h;
	return [(w-tw)/2, (h-th)/2, (w-tw)/2 + tw, (h-th)/2 + th];
    };
    function getCropCoords(c, forBackend) {
	if (!c) c = jCrop.tellSelect();
	d = {};
	d.x = (c.x / previewRatio);
	d.y = (c.y / previewRatio);
	d.w = (c.w / previewRatio);
	d.h = (c.h / previewRatio);
	if (forBackend) return d;
	var rx = currentCropSize.w / d.w;
	var ry = currentCropSize.h / d.h;
	d.w = Math.round(rx * previewIniW) * currentCropSize.ratio;
	d.h = Math.round(ry * previewIniH) * currentCropSize.ratio;
	d.x = Math.round(rx * d.x) * currentCropSize.ratio;
	d.y = Math.round(ry * d.y) * currentCropSize.ratio;
	return d;
    }
    /* -------------------- edit image --------------------- */
    var currentEditOrd;// order number of the currently edited image
    tn3.editImage = function(id) {
	currentEditOrd = tn3.getID(id).ord;
	ajaxit({
	    tn3_action: "load_form",
	    form: "edit_image.html"
	}, function(data) {
	    tn3.parseForm(data, "image");
	    tn3.blockDialog(data, form.w, form.h, tn3.editImageFormInit, [id], function() {
		tn3.getForm(tn3.data[currentEditOrd].id);
		if (form.save !== undefined) formSave("images");
	    });
	    
	});
    }
    // when image form inits
    tn3.editImageFormInit = function(id)
    {
	var $sel = $("#tn3_content_select"),
	    $contc = $sel.parent().parent().parent(),
	    showc = function(type) {
		$contc.find(".tn3_con").hide();
		if (type) 
		    $contc.find("#tn3_content_" + type).show();
	    };
	$sel.change(function(e){
	    showc(e.target.value);
	});
	tn3.setEditImageProps(id);
    }
    // after init and on next/prev
    tn3.setEditImageProps = function(id)
    {
	if (id == "next") {
	    // run getForm to keep edited values before moving to next
	    tn3.getForm(tn3.data[currentEditOrd].id);
	    if (tn3.data[currentEditOrd + 1] != undefined && tn3.data[currentEditOrd + 1].type == "image") 
		id = tn3.data[currentEditOrd + 1].id;
	    else return;
	} else if (id == "prev") {
	    // run getForm to keep edited values before moving to prev
	    tn3.getForm(tn3.data[currentEditOrd].id);
	    if (currentEditOrd > 0) 
		id = tn3.data[currentEditOrd - 1].id;
	    else return;
	}
	var dta = tn3.getID(id),
	    ctype = dta.content_type,
	    $c = $('.blockPage'),
	    val;
	tn3.preloadImage( tn3.getImagePath(dta.path, 2), $c.find('.tn3-img'), function($img) {
	    $img.width(60).height(60);
	});

	if (form.save && form.save[id] && form.save[id].content_type) ctype = form.save[id].content_type.value;
	$.each(form.fields, function(i, v) {
	    val = "";

	    if ( dta[v] !== undefined ) val = dta[v];
	    if (form.save && form.save[id] && form.save[id][v]) val = form.save[id][v].value;
	    //if (v == "content_type" && val !="") ctype = val;
	    $c.find('[name="tn3_image_' + v + '"]').each(function() {
		if (v == "content" && ctype != $(this).parent().attr("id").substr(12)) this.value = "";
		else {
		    this.value = val;
		}
	    });
	});
	$c.find("#tn3_content_select").val(ctype).change();
	currentEditOrd = dta.ord;
	
    }
    function formSave(ttype) {
	var pdata = {
		bulk_type: ttype,
		tn3_action: "save_data",
		_wpnonce: $('#_wpnonce').val(),
		data: form.save
	    };
	ajaxit(pdata, function(res) {

	    $('.wp-list-table').find('input[name="tn3_images"]').each(function() {
		var newv = form.save[this.value];
		if (newv === undefined) return;
		var ithis = $(this);
		$.each(newv, function(i, v) {
		    ithis.parent().parent().find('.' + i).each(function() {
			if (i == "title") {
			    $(this).find('strong a').text( v.value );
			} else $(this).html( v.value );
		    });
		});
	    });
	    var dta;
	    $.each (pdata.data, function(i, v) {
		dta = tn3.getID(i);
		$.each(v, function(vi, vv) {
		    dta[vi] = vv.value;
		});
	    });
	    confirmDialog(tn3L18n.success);
	});
    }

    function initSkinPresets()
    {
	var $combo = $('#tn3_skin_presets'),
	    cur;
	$combo.change(function() {
	    cur = tn3.skins[$combo.val()];
	    $.each(cur, function (i, v) {
		switch (i) {
		    case 'image':
			$.each(v, function (j, k) {
			    setInputValue("image", j, k);
			});
			break;
		    case 'thumbnailer':
			$.each(v, function (j, k) {
			    setInputValue("thumbnailer", j, k);
			});
			break;
		    default:
			 setInputValue("general", i, v);
			break;
		}
		
		
	    });
	});
	var setInputValue = function(sec, nme, val) {
	    var id = "#tn3_skin_" + sec + "_" + nme + "_" + (typeof val).substring(0,1);
	    if (typeof val == "object") val = val.join(",");
	    $(id).val(val);
	}
	var btnState = "addnew",
	    $pname = $('<input id="tn3_skin_presets" name="tn3_skin_presets" value="'+tn3L18n.newpreset+'" type="text" size="16">'),
	    $del = $('#tn3_skin_presets_del').click(function(e) {
		$('input[name="tn3_skin_presets_action"]').val('delete');
	    }),
	    $btn = $('#tn3_skin_presets_btn').click(function(e) {
		if (btnState == "addnew") {
		    $combo.detach();
		    $btn.parent().prepend($pname);
		    $pname.focus();
		    btnState = "cancel";
		} else {
		    $pname.detach();
		    $btn.parent().prepend($combo);
		    btnState = "addnew";
		}
		$btn.val(tn3L18n[btnState]);
		e.preventDefault();
	    });		
    }
    function initTransitionPresets()
    {
	var $combo = $('#tn3_transition_presets'),
	    cur;
	var btnState = "addnew",
	    $pname = $('<input id="tn3_transition_presets" name="tn3_transition_presets" value="'+tn3L18n.newpreset+'" type="text" size="16">'),
	    $del = $('#tn3_transition_presets_del').click(function(e) {
		$('input[name="tn3_transition_presets_action"]').val('delete');
	    }),
	    $btn = $('#tn3_transition_presets_btn').click(function(e) {
		if (btnState == "addnew") {
		    $combo.detach();
		    $btn.parent().prepend($pname);
		    $pname.focus();
		    btnState = "cancel";
		} else {
		    $pname.detach();
		    $btn.parent().prepend($combo);
		    btnState = "addnew";
		}
		$btn.val(tn3L18n[btnState]);
		e.preventDefault();
	    });		
    }

    function onTN3Button(tinyED)
    {
	
	$( "#tn3-tabs" ).tabs({
	    show: function(e, ui) {
		// source
		if (ui.index == 0) {

		// options
		} else {
		    //loadAdminForm("post.html", $('#tn3-tab-options'), "selSkins");
		}
	    }
	});

	var $dia = $('#tn3-dialog'),
	    submit = {},
	    $sel, $this,
	    alb, skin, autop, firalb, out,
	    scb = function(ids) {
		$sel.text($sel.data('title') + "(" + ids.length + ")");
		submit.ids = ids.toString();
	    },
	    $cont = $dia.find('.tn3-source'),
	    $xml_form = $('#tn3-xml-form'),
	    $flickr_form = $('#tn3-flickr-form'),
	    $picasa_form = $('#tn3-picasa-form'),
	    $facebook_form = $('#tn3-facebook-form'),
	    showForm = function(fname) {
		$cont.empty();
		$cont.append(eval("$"+fname+"_form"));
		submit = {origin: fname, plugin: true};
	    },
	    selectForm = function(fname, multi) {
		ajaxSelect(fname, scb, multi, $cont, true, {}, true);
		submit = {origin: fname};
	    },
	    loadFlickr = function(key) {
		var targ = $dia.find('#tn3-flickr-combo-sets'),
		    org = (key=="sets")? "photosets" : "galleries",
		    orgone = (key=="sets")? "photoset" : "gallery",
		    data = {
			format:"json",
			api_key:$dia.find('input[name=tn3-flickr-api_key]').val(),
			user_id:$dia.find('input[name=tn3-flickr-user_id]').val(),
			method: "flickr." + org + ".getList"
		    }
		targ.empty().append($("<option></option>").attr("value", "").text("All")).show().attr('disabled', '');
		targ.attr("name", "tn3-flickr-" + orgone + "_id");
		$.ajax({
		    url: "http://api.flickr.com/services/rest/?",
		    type :"GET",
		    dataType: 'jsonp',
		    cache :false,
		    context :this,
		    jsonp: "jsoncallback",
		    success: function(data) {
			if (data.stat == "fail") {
			    var desc = "flickr: " + data.message;
			    return;
			}
			targ.removeAttr('disabled');
			var dta = data[org][orgone];
			$.each(dta, function(i, v) {
			    targ.append($("<option></option>").attr("value", v.id).text(v.title._content));
			});
		    },
		    error: function(XMLHttpRequest, textStatus, errorThrown) {
		    },
		    data: data
		});
	    },
	    loadPicasa = function() {
		var req = {
		    kind: "album",
		    alt: "json-in-script"
		}, targ = $picasa_form.find('#tn3-picasa-combo-album');
		targ.empty().append($("<option></option>").attr("value", "").text("Loading...")).show().attr('disabled', '');		
		$.ajax({
		    url: "http://picasaweb.google.com/data/feed/api/user/" + $picasa_form.find('input[name=tn3-picasa-userID]').val(),
		    type :"GET",
		    dataType: 'jsonp',
		    cache :false,
		    context :this,
		    jsonp: "callback",
		    success: function(data) {
			targ.empty();
			$.each(data.feed.entry, function(i, v) {
			    targ.append($("<option></option>").attr("value", v.gphoto$id.$t).text(v.title.$t));
			})
			targ.removeAttr('disabled');
		    },
		    data: req
		});
	    },
	    loadFacebook = function($uid, $aid) {
		var uid = $uid.find('input').val(),
		    targ = $aid.find('select');
		targ.empty().append($("<option></option>").attr("value", "").text("Loading...")).show().attr('disabled', '');		
		$.ajax({
		    url: "http://graph.facebook.com/" + uid + "/albums",
		    type: "GET",
		    dataType: 'jsonp',
		    cache: false,
		    context: this,
		    jsonp: "callback",
		    success: function(data) {
			$.each(data.data, function(i, v) {
			    targ.append($("<option></option>").attr("value", v.id).text(v.name));
			})
			targ.removeAttr('disabled');
		    },
		    data: {offset:0,limit:299}
		});
	    }		
	$xml_form.detach();
	$flickr_form.detach();
	$picasa_form.detach();
	$picasa_albumID = $picasa_form.find(".tn3-hidden");
	$facebook_form.detach();

	$dia.find('.tn3-source-nav li').click(function(e) {
	    $cont.data('ids', []);
	    $this = $(this);
	    $this.css("background-color", "#aaa");
	    if ($sel) {
		$sel.css("background-color", "#eee");
		$sel.text($sel.data('title'));
	    }
	    $sel = $this;
	    switch ($this.data('title')) {
		case "Images":
		    selectForm('image', true);
		    break;
		case "Albums":
		    selectForm('album', true);
		    break;
		case "Gallery":
		    selectForm('gallery', false);
		    break;
		case "XML":
		    showForm("xml");
		    break;
		case "Flickr":
		    showForm("flickr");
		    $dia.find('#tn3-flickr-combo-source').change(function() {
			if  (this.value == "sets" || this.value == "galleries") {
			    loadFlickr(this.value);
			} else $dia.find('#tn3-flickr-combo-sets').hide();
		    }).change();
		    break;
		case "Picasa":
		    showForm("picasa");
		    $picasa_form.find('#tn3-picasa-combo-source').change(function(e) {
			if (e.target.value == "album") {
			    $picasa_albumID.show();
			    loadPicasa($picasa_form);
			} else $picasa_albumID.hide();
		    });
		    break;
		case "Facebook":
		    showForm("facebook");
		    var id_tit = $facebook_form.find(".tn3-title-id"),
			uid = $facebook_form.find(".tn3-facebook-user-id"),
			aid = $facebook_form.find(".tn3-facebook-album-id");
		    $facebook_form.find("#tn3-facebook-combo-source").change(function(e) {
			if (this.value == "albums") {
			    id_tit.text("User ID:");
			    uid.show();
			    uid.find('input').attr("name", "tn3-facebook-ID");
			    aid.hide();
			    aid.find('select').attr("name", "tn3-facebook-aID").attr('disabled', '');
			} else {
			    id_tit.text("Album ID:");
			    uid.hide();
			    uid.find('input').attr("name", "tn3-facebook-aID").attr('disabled', '');
			    aid.show();
			    aid.find('select').attr("name", "tn3-facebook-ID");
			    loadFacebook(uid, aid);
			}
		    });
		    break;
		default:

		    break;
	    }
	}).hover(function () {
	    if (this != $sel.get(0)) $(this).css("background-color", "#aaa")
					    .css("cursor", "pointer");
	}, function () {
	    if (this != $sel.get(0)) $(this).css("background-color", "#eee");
	}).each(function (i, v) {
	    $(this).data('title', $(this).text());
	    if (i == 0) $(this).click();
	});
	$dia.find('#tn3-ok').click(function() {
	    var out = {origin: submit.origin};
	    if (submit.plugin !== undefined) mergeFormVals($cont, out, 5 + submit.origin.length);
	    else if (submit.ids !== undefined ) {
		if (submit.ids == "") return;
		out.ids = submit.ids;
	    }
		
	    mergeFormVals( $dia.find('#tn3-tab-options'), out, 9);
	    removeDefaults(out);
	    var tout = "[tn3";
	    $.each(out, function(i, v) {
		if (v != "") tout += " " + i + '="' + v + '"';
	    });
	    tout += "]";
	    tinyED.windowManager.close();
	    tinyED.focus();
	    tinyED.selection.setContent(tout);
	});
	$dia.find('#wp-link-cancel').click(function() {
	    tinyED.windowManager.close();
	});
	var $wfield = $dia.find('input[name=tn3-post-width]'),
	    $hfield = $dia.find('input[name=tn3-post-height]');
	$dia.find('#tn3-select-skin').change(function() {
	    $wfield.attr("value", tn3.skinPresets[this.value].width);
	    $hfield.attr("value", tn3.skinPresets[this.value].height);
	});
	
	var mergeFormVals = function($c, va, len) {
	    $c.find(':input').each(function(e) {
		$this = $(this);
		if ( $this.is(":checkbox") && !$this.is(":checked") ) return;
		if ( $this.is(":radio") && ( !$this.is(":checked") || this.value == "disabled" ) ) return;
		if ( $this.is(":disabled") ) return;
		va[ $this.attr("name").substr(len) ] = $this.val();
	    });
	},
	    removeDefaults = function(o) {
		var remo = $.extend({}, tn3.defaults.general);
		if (o.origin == "flickr" || o.origin == "picasa") remo = $.extend(remo, tn3.defaults[o.origin]);
		$.each(remo, function(i, v) {
		    if (o[i] == v && i != "origin") delete(o[i]);
		});
	    };
    }
    function loadAdminForm(name, $cont, replace)
    {
	ajaxit({
	    tn3_action: "load_form",
	    form: name
	}, function(data) {
	    $cont.empty();
	    data = data.split("###" + replace).join(tn3[replace]);
	    $cont.html(data);
	}, {});
    }

    

})(jQuery);


