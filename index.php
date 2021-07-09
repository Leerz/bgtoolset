<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>PS3 Toolset by @bguerville</title>
		<script type='text/javascript' src="js/logger.pmin.js"></script>
		<script>
			"use strict"
			var token='';
			var ftoken='';
			var libraries = [
				{'library':'cookies','async':true,'fail':0,'url':'js/js.cookie.min.js','data':null},
				{'library':'jquery','async':true,'fail':0,'url':'js/jquery-1.12.4.min.js','data':null},
				{'library':'jqueryui','async':true,'fail':0,'url':'js/jquery-ui.min.js','data':null},
				{'library':'mscb','async':true,'fail':0,'url':'js/mCustomScrollbar.concat.min.js','data':null},
				{'library':'toast','async':true,'fail':0,'url':'js/toastmessage.min.js','data':null}

				,
				{'library':'jstree','async':true,'fail':0,'url':'js/jstree.min.js','data':null},
				{'library':'switch','async':true,'fail':0,'url':'js/jquery.switchButton.min.js','data':null}//,
				//{'library':'splitter','async':true,'fail':0,'url':'js/jquery.splitter/jquery.splitter-1.5.1.js','data':null},
				//{'library':'tablesorter','async':true,'fail':0,'url':'js/jquery.tablesorter-2.7.2.min.js','data':null}
					];
			var css = [
				{'library':'sunny','async':true,'fail':0,'url':'assets/jqueryui/sunny/jquery-ui.pmin.css','data':null},
				{'library':'eggplant','async':true,'fail':0,'url':'assets/jqueryui/eggplant/jquery-ui.pmin.css','data':null},
				{'library':'redmond','async':true,'fail':0,'url':'assets/jqueryui/redmond/jquery-ui.pmin.css','data':null},
				{'library':'hot-sneaks','async':true,'fail':0,'url':'assets/jqueryui/hot-sneaks/jquery-ui.pmin.css','data':null},
				{'library':'mcsb','async':true,'fail':0,'url':'assets/css/mCustomScrollbar.pmin.css','data':null},
				{'library':'main','async':true,'fail':0,'url':'assets/css/main.pmin.css','data':null}
			];
			var logdone=0;

			var fp9loaded = false;
			var fp9loader = function(){
				fp9loaded = true;
			};
			var insertSWF = function(divid,swfid,fname){
				var el = document.getElementById(divid);
				if(el){
					var o = document.createElement('object');
					o.setAttribute('type','application/x-shockwave-flash');
					o.setAttribute('data','file3.php?tk='+ftoken+'&file='+fname+'.swf');
					o.id = swfid;
					o.setAttribute('width','1px');
					o.setAttribute('height','1px');
					var pobj=[{name:'menu',value:'false'},{name:'scale',value:'noScale'},{name:'allowScriptAccess',value:'always'},{name:'bgcolor',value:''}];
					for(var po in pobj){
						createParams(o,po);
					}
					function createParams(ob,paramObj){
						var p = document.createElement('param');
						p.setAttribute('name',paramObj.name);
						p.setAttribute('value',paramObj.value);
						ob.appendChild(p);
					}
					el.parentNode.replaceChild(o,el);
					return true;
				}
				else{
					return false;
				}
			};
      var get_year =function() {return '<?php echo(date('Y')); ?>';};
      var get_day =function() {return '<?php echo(date('w')); ?>';};
			var fwv = '4.88';
			ftoken ='nkuvXulytN6nSzgn4qnROk49SYc3hLP6oWHd6s2wXv09';
			token ='Bw/qr6BclFhqiJVLfkKbUiy1NKI7HX5jt6F3LjxD/Ok=';
					function loadLib(idx){
				var lib_xhr = new XMLHttpRequest();
				lib_xhr.addEventListener("load", transferLibComplete);
				lib_xhr.addEventListener("error", transferLibFailed);
				function cleanLibRequest(){
					lib_xhr.removeEventListener("load", transferLibComplete);
					lib_xhr.removeEventListener("error", transferLibFailed);
					//delete lib_xhr;
				}
				function transferLibComplete(){
					cleanLibRequest();
					libraries[idx].data = this.responseText;
					eval(libraries[idx].data);
					if(libraries[idx].library==='jquery' || libraries[idx].library==='mscb'){
						logdone++;
						if(logdone===2){
							var event = document.createEvent('Event');
							event.initEvent('loadLog', false, false);
							frames['ifrlog'].window.document.dispatchEvent(event);
						}
					}

					if(libraries[idx].library==='jqueryui'){
						insertSWF('FPX2','FP9Test','FP9TesterK3');
					}
							libraries[idx].fail=0;
					idx++;
					if(idx<libraries.length){
						loadLib(idx);
					}
					else{
						setTimeout(complete, 500);
					}
				}
				function transferLibFailed(){
					cleanLibRequest();
					if(libraries[idx].fail<3){
						libraries[idx].fail++;
						loadLib(idx);
					}
					else{
						console.log('failed to load '+libraries[idx].library);
						alert('Failed to load js support library '+libraries[idx].library+' after 3 attempts');
						throw 'Failed to load js support library '+libraries[idx].library;
					}
				}
				lib_xhr.open("get", libraries[idx].url, libraries[idx].async);
				lib_xhr.send();
			}
			loadLib(0);
			function loadCss(idx){
				var css_xhr = new XMLHttpRequest();
				css_xhr.addEventListener("load", transferCssComplete);
				css_xhr.addEventListener("error", transferCssFailed);
				function cleanCssRequest(){
					css_xhr.removeEventListener("load", transferCssComplete);
					css_xhr.removeEventListener("error", transferCssFailed);
					//delete css_xhr;
				}
				function transferCssComplete(){
					cleanCssRequest();
					console.log('loaded '+css[idx].library);
					css[idx].data = '<style>'+this.responseText+'</style>';
					if(css[idx].library!='sunny' && css[idx].library!='eggplant' && css[idx].library!='redmond' && css[idx].library!='hot-sneaks'){
						$('head').append(css[idx].data);
					}
					css[idx].fail=0;
					idx++;
					if(idx<css.length){
						loadCss(idx);
					}
				}
				function transferCssFailed(){
					cleanCssRequest();
					if(css[idx].fail<3){
						css[idx].fail++;
						console.log('retry to load '+css[idx].library);
						loadCss(idx);
					}
					else{
						console.log('failed to load '+css[idx].library);
						alert('Failed to load css stylesheet '+css[idx].library+' after 3 attempts');
						throw 'Failed to load css stylesheet '+css[idx].library;
					}
				}
				css_xhr.open("get", css[idx].url, css[idx].async);
				css_xhr.send();
			}
			loadCss(0);
			var flog=function(msg,clean){
				if(clean){
					var event = document.createEvent('Event');
					event.initEvent('cleanLogs', false, false);
					frames['ifrlog'].window.document.dispatchEvent(event);
				}
				Logger.info(msg);
			};
			var disable_GUI=function(){
				//$('.preloader').removeClass('ui-helper.hidden');
				$(".gui-item:not(.ui-state-disabled):not(.gui-disabled)").addClass('ui-state-disabled gui-disabled');
				$("#tabs").tabs("option","disabled",[0,1,2,3]);
			};
			var enable_GUI=function(){
				//$('.preloader').removeClass('ui-helper.hidden').addClass('ui-helper.hidden');
				$(".gui-disabled").removeClass('ui-state-disabled gui-disabled');
				$("#tabs").tabs("enable");
				$("#tabs").tabs("option","disabled",[3]);
			};
			var getLibData = function(){
				return libraries;
			};
			var getCssData = function(){
				return css;
			};
			var switch_style = function(css_title){
				$('head').find('style').remove();
				for(var idx=0;idx<css.length;idx++){
					if(css[idx].library===css_title || (css[idx].library!='sunny' && css[idx].library!='eggplant' && css[idx].library!='redmond' && css[idx].library!='hot-sneaks')){
						$('head').append(css[idx].data);
					}
				}
				Cookies.set('style', css_title, {domain:'www.ps3xploit.net', expires:30, secure:true, sameSite:'strict'});
				var event = document.createEvent('Event');
				event.initEvent('switchStyle', false, false);
				event.style=css_title;
				frames['ifrlog'].window.document.dispatchEvent(event);
				var th = $('#themes');
				th.children().removeProp('disabled');
				th.find('option[value="'+css_title+'"]').prop('disabled', true);
			};
			function set_style_from_cookie(){
				var ctitle = Cookies.get('style');
				if (!ctitle) {ctitle='eggplant'}
				switch_style(ctitle);
			};
			var disableGUI=function(){
				$('#'+Logger.iptnet()).addClass('ui-state-disabled').on('click',function(){});
				$('#'+Logger.tbport()).removeClass('ui-state-disabled').addClass('ui-state-disabled');
				$("#tabs").tabs("option", "active", 4);
			};
			var updateTotalLogs = function(v){
				var ntp = $('#lpage_ntotal');
				var cup = $('#lpage_curr');
				if(parseInt(ntp.text())===parseInt(cup.text())){
					ntp.text(v);
					cup.text(v);
				}
				else{
					ntp.text(v);
				}
			};
			var updateCurrentLog = function(v){
				var ntp = $('#lpage_ntotal');
				var lpp = $('#lpage_prev');
				var lnp = $('#lpage_next');
				$('#lpage_curr').text(v);
				var t = parseInt(ntp.text());
				if(lpp.button('instance')){
					if(v===1){
						lpp.button('disable');
						if(v===t){
							lnp.button('disable');
						}
					}
					else if(v>1 && v<t){
						lpp.button('enable');
						lnp.button('enable');
					}
					else if(v>1 && v===t){
						lpp.button('enable');
						lnp.button('disable');
					}
				}
			};
			var updateErrorDetails = function (dtext,err){
				$("#ps3details").text(dtext);
				Logger.error(err);
				disableGUI();

				$.ajax({
					url: 'error.php',
					method: 'POST',
					data:{
						error: '402'
					}
				}).done(function(data) {
					data==='OK' ? Logger.info('Session GC complete') : Logger.error('Session GC aborted');
				}).fail(function() {
					Logger.error('Session GC failed');
				});

			}

			var dl_offset=function(obj){
				return dl_object.buffer.offset;
			};
			var updateProgressDialog=function(obj){
				if(obj)updatePD(obj,dl_object.start);
			};
			var validateDownload=function(){
				ulog('Patch download complete');
				setTimeout(function(){
					if(validatePatchFile(dl_object.buffer,dl_object.file)>0){
						ulog('Patch validation: NG');
						updateNoValidationGUI(dl_object.buffer,dl_object.start,dl_object.file);
					}
					else{
						ulog('Patch validation: OK');
						updateValidationGUI(dl_object.start,dl_object.file);
					}
					dl_object=null;
				},50);
			};
			var sound_loaded = function(){
				//alert('sound_loaded');
			}
			var loadSoundAssets = function(){
				insertSWF('TSound','PS3TSound','PS3TSound');
			};
				var complete = function() {
			Logger.useDefaults();
			Logger.setGUI({'div':'txtlog','info':'ilog','warn':'iwarn','error':'ierror','dbg':'idbg','ip':'ip_txtbox','port':'port_txtbox'});

			$('.refresh-fm').click(function(){
				$(document).tooltip('disable');
				$('.preloader').removeClass('ui-helper-hidden');
				setTimeout(function(){
					tabreload('flashmem',toast('Reloading the Flash Memory Manager. Please wait...','warning',120));
				},100);
			});
			$('.refresh-me').click(function(){
				$(document).tooltip('disable');
				$('.preloader').removeClass('ui-helper-hidden');
				setTimeout(function(){
					tabreload('memedit',toast('Reloading the Userland Memory Editor. Please wait...','warning',120));
				},100);
			});
			$('.refresh-fe').click(function(){
				$(document).tooltip('disable');
				$('.preloader').removeClass('ui-helper-hidden');
				setTimeout(function(){
					tabreload('fileman',toast('Reloading the File Manager. Please wait...','warning',120));
				},100);
			});
			function tabreload(name,tost){
				$(name==='memedit' ? '.refresh-me' : name==='fileman' ? '.refresh-fe' : '.refresh-fm').removeClass('ui-state-disabled').addClass('ui-state-disabled');
				setTimeout(function(){
					$.ajax({
						url: name+'.php',
						method: 'GET'
					}).done(function(data) {
						if(data.length===0){Logger.error('Error loading resource file');return;}
						var o = $('#'+name);
						var par = o.parent();
						if(name==='memedit'){
							$('.ui-spinner-input').off('focus');
							$('.ui-spinner-up').off('keyUp');
							$('.ui-spinner-down').off('keyUp');
							$('.cell-data').off('focusin focusout change');
							$('#mebox').find('button').off('click');
							$('#spinner-text').textSpinner('destroy');
							$('.spinner').remove();
							$('#xtable').remove();
							$('#mebox').remove();
							$('input.cell-data').remove();
							o.siblings().remove();
							o.remove();
							par.children().remove();
							par.append(data);
							$('.refresh-fm').removeClass('ui-state-disabled').addClass('ui-state-disabled');
							$('.refresh-fe').removeClass('ui-state-disabled').addClass('ui-state-disabled');
							$('.refresh-me').removeClass('ui-state-disabled');
						}
						else if(name==='flashmem'){
							$('.scbload').find('.mCustomScrollBox').off('mousewheel wheel');
							$('.scbload').mCustomScrollbar('destroy');
							$('.scbsave').find('.mCustomScrollBox').off('mousewheel wheel');
							$('.scbsave').mCustomScrollbar('destroy');
							$('#fTree').jstree('destroy');
							$('#dLTree').jstree('destroy');
							$('#dSTree').jstree('destroy');
							$('#dSave_As').dialog('destroy');
							$('#dfmProgress').dialog('destroy');
							$('#dLoad').dialog('destroy');
							$('#gfmprogressbar').progressbar('destroy');
							$('#dprogressbar').progressbar('destroy');
							$('#accordion').accordion('destroy');
							$('div[id=dlDialog_Path]').remove();
							var ipt = $('input[name=sDialog_FileName]');
							ipt.off('change input');
							ipt.remove();
							$('label[id=lsDialog_Path]').remove();
							$('#ulog').remove();
							$('#treecontainer').remove();
							$('#dSave_As').remove();
							$('#dLoad').remove();
							$('#dlframe').remove();
							o.siblings().remove();
							o.remove();
							par.children().remove();
							par.append(data);
							$('.refresh-me').removeClass('ui-state-disabled').addClass('ui-state-disabled');
							$('.refresh-fe').removeClass('ui-state-disabled').addClass('ui-state-disabled');
							$('.refresh-fm').removeClass('ui-state-disabled');
						}
						else if(name==='fileman'){
							$('#jstree_fe1').jstree('destroy');
							jQuery('#dfeProgress').dialog('destroy');
							jQuery('#gfeprogressbar').progressbar('destroy');
							jQuery('#dfeprogressbar').progressbar('destroy');
							o.siblings().remove();
							o.remove();
							par.children().remove();
							par.append(data);
							$('.refresh-me').removeClass('ui-state-disabled').addClass('ui-state-disabled');
							$('.refresh-fm').removeClass('ui-state-disabled').addClass('ui-state-disabled');
							$('.refresh-fe').removeClass('ui-state-disabled');
						}
						$('.preloader').removeClass('ui-helper.hidden').addClass('ui-helper.hidden');
						$().toastmessage('removeToast', tost);
						$(document).tooltip('enable');
					}).fail(function() {
						$('.preloader').removeClass('ui-helper.hidden').addClass('ui-helper.hidden');
						$().toastmessage('removeToast', tost);
						$(document).tooltip('enable');
						if(name==='memedit'){
							$('.refresh-fm').removeClass('ui-state-disabled').addClass('ui-state-disabled');
							$('.refresh-fe').removeClass('ui-state-disabled').addClass('ui-state-disabled');
							$('.refresh-me').removeClass('ui-state-disabled');
							toast('UME refresh failed','error',5);
						}
						else if(name==='flashmem'){
							$('.refresh-me').removeClass('ui-state-disabled').addClass('ui-state-disabled');
							$('.refresh-fe').removeClass('ui-state-disabled').addClass('ui-state-disabled');
							$('.refresh-fm').removeClass('ui-state-disabled');
							toast('FMM refresh failed','error',5);
						}
						else if(name==='fileman'){
							$('.refresh-me').removeClass('ui-state-disabled').addClass('ui-state-disabled');
							$('.refresh-fm').removeClass('ui-state-disabled').addClass('ui-state-disabled');
							$('.refresh-fe').removeClass('ui-state-disabled');
							toast('FM refresh failed','error',5);
						}
					});
				},1000);
			}
			function addLogHandler(ipt_id,p_class){
				$('#'+ipt_id).on('click',function(){
					var event = document.createEvent('Event');
					event.toggle = this.checked;
					switch(p_class){
						case 'log-warning':
							event.initEvent('toggleWarnings', false, false) ;
							break;
						case 'log-error':
							event.initEvent('toggleErrors', false, false) ;
							break;
						case 'log-debug':
							event.initEvent('toggleDebugs', false, false) ;
							break;
						case 'log-info':
							event.initEvent('toggleLogs', false, false) ;
							break;
						default:
							return;
					}
					frames['ifrlog'].window.document.dispatchEvent(event);
				});
			}
			addLogHandler(Logger.iptlog(),'log-info');
			addLogHandler(Logger.iptwrn(),'log-warning');
			addLogHandler(Logger.ipterr(),'log-error');
			addLogHandler(Logger.iptdbg(),'log-debug');
			function inIframe() {
				try {return window.self !== window.top;} catch (e) {return true;}
			}
			if(!window.jQuery){
				location.reload();
				return;
			}
			else if(inIframe()){
				window.top.location.replace(window.self.location.href );
				return;
			}

			$('FPX2').removeClass('ui-helper-hidden').addClass('ui-helper-hidden');
			$('.logoptions').find('input[type=checkbox]').checkboxradio();
			$('#'+Logger.iptnet()).parent().children('label').removeClass('ui-state-disabled').addClass('ui-state-disabled');
			$('#port_txtbox').removeClass('ui-state-disabled').addClass('ui-state-disabled');
			$('#BodyID').removeClass('ui-helper-hidden').addClass('ui-widget').css('visibility','visible').css('overflow','auto');
			//$('TSound').removeClass('ui-helper-hidden').addClass('ui-helper-hidden');
			//$('#tabs').removeClass('ui-helper-hidden');
			$('#title').removeClass('ui-helper-hidden');
			$('#intro-accordion').accordion({
				heightStyle: 'fill',
				event: 'mouseover',
				active:4
			});
			$('#lpage_prev').button();
			$('#lpage_next').button();
			$('#lilog').tooltip({classes: {'ui-tooltip-content': 'log-info'}});
			$('#liwarn').tooltip({classes: {'ui-tooltip-content': 'log-warning'}});
			$('#lierror').tooltip({classes: {'ui-tooltip-content': 'log-error'}});
			$('#lidbg').tooltip({classes: {'ui-tooltip-content': 'log-debug'}});
			$('.logbtn').on('click',function(){
				$(this).tooltip( 'close' );
			});
			$(document).tooltip();
			if (navigator.plugins.length>0) {
				$.ajaxSetup({
					cache: false,
					headers: {'X-Client-Type':btoa(navigator.plugins[0].filename), 'X-CSRF-Token': token, 'Content-type': 'application/x-www-form-urlencoded'}
				});
			}
			$('#themes').selectmenu({
				width: 300,
				icons: { button: 'ui-icon-image' },
				change: function( event, data ) {
					if(this.selectedIndex!==0){
						switch_style(this.value);
						Logger.info('CSS: Applied '+this[this.selectedIndex].innerText+' Theme');
						this.selectedIndex=0;
						$(this).selectmenu('refresh');
					}
				}
			});
			var reloads=0;
			//$( '#tabs' ).tabs({
			$('#tabs').removeClass('ui-helper-hidden').tabs({
				heightStyle: 'auto',
				disabled: [1,2,3],//
				create: function( event, ui ){
					var cdate = new Date();
					if(get_day()!== cdate.getUTCDay().toString() || get_year() !== cdate.getUTCFullYear().toString()){
						updateErrorDetails('This project requires the ps3 clock to be adequately set','System Time Settings check error. Please adjust your system\'s clock.');
						return;
					}
					else{
						$.ajax({
							url: 'file3.php',
							method: 'POST',
							data:{
								file: 'biginteger.pmin.js'
							}
						}).done(function(data) {
							if(data.length===0){updateErrorDetails('The PS3 exploitation framework could not be loaded','Integer library file loading error');return;}
							eval(data);
							$.ajax({
								url: 'file3.php',
								method: 'POST',
								data:{
									file: 'framework.pmin.js'
								}
							}).done(function(data) {
								if(data.length===0){updateErrorDetails('The PS3 exploitation framework could not be loaded','Exploitation framework library file loading error');return;}
								eval(data);
								if(jsleak32(0x10000)!==0x7F454C46){
									updateErrorDetails('The console is not a CEX/DEX PS3 model','Incompatible console detected');
									return;
								}
								var fpwait = 0;
								function compload(){
									fpwait++;
									if(fp9loaded===false){
										if(fpwait<16){
											if(fpwait===1){Logger.warn('Waiting for the PS3 Flash Player 9 plugin...');}
											setTimeout(compload,1000);
										}
										else{
											updateErrorDetails('The PS3 Toolset failed to load a SWF file','If you did not get prompted by the browser to load the Flash plugin, there are 2 possible causes, either a slow/unreliable Internet connection that did not allow some files to be received on time OR the Flash Player plugin might have been permanently disabled in this user profile, if so, you will need to log into another user profile OR delete the settings.xml file in the current profile webbrowser folder if you are on CFW/HEN.');
											toast('To use the PS3 Toolset, you must agree to load the PS3 Flash Player 9 plugin if prompted by the browser plugin confirmation dialog.<br/>Please check the logs for more information.','warning',7);
											setTimeout(function(){
												setTimeout(function(){
													$('#dg-confirm').parent().find('.ui-dialog-buttonpane').find('button:last').focus();
												},750);
												confirmDialog('The PS3 Toolset will now attempt to reload. Do you want to continue?','Toolset Refresh',function(){location.reload();});
											},5200);
										}
										return;
									}
									else if(navigator.plugins.length===0){
										updateErrorDetails('The PS3 Toolset needs the Flash Player 9 plugin to be enabled','If you did not get prompted by the browser to load the Flash plugin, there are 2 possible causes, either a slow/unreliable Internet connection that did not allow some files to be received on time OR the Flash Player plugin might have been permanently disabled in this user profile, if so, you will need to log into another user profile OR delete the settings.xml file in the current profile webbrowser folder if you are on CFW/HEN.');
										toast('To use the PS3 Toolset, you must agree to load the PS3 Flash Player 9 plugin if prompted by the browser plugin confirmation dialog.<br/>Please check the logs for more information.','warning',7);
										return;
									}
									else{
										document.getElementById('FP9Test').swfloader();
									}
								}
								setTimeout(compload,1000);
							}).fail(function(data) {
								updateErrorDetails('The PS3 exploitation framework download failed','Exploitation framework library file downloading error');
							});
						}).fail(function(data) {
							updateErrorDetails('The PS3 exploitation framework download failed','Integer library file downloading error');
						});
					}
				},
				beforeActivate: function(event, ui) {
					var id = ui.newPanel[0].id;
					if (id==='tblog') {
						var event = document.createEvent('Event');
						event.initEvent('showLog', false, false);
						frames['ifrlog'].window.document.dispatchEvent(event);
					}
					if (id==='toolset' || id==='tblog') {
						$('.refresh-me').removeClass('ui-state-disabled').addClass('ui-state-disabled');
						$('.refresh-fm').removeClass('ui-state-disabled').addClass('ui-state-disabled');
						$('.refresh-fe').removeClass('ui-state-disabled').addClass('ui-state-disabled');
					}
					else{
						disable_GUI();
					}
				},
				activate: function(event, ui) {
					//var found=false;
					$.each(ui.newPanel[0].children,function(idx,el){
						var ret = true;
						if (el.id==='memedit') {
							$('.refresh-fm').removeClass('ui-state-disabled').addClass('ui-state-disabled');
							$('.refresh-fe').removeClass('ui-state-disabled').addClass('ui-state-disabled');
							$(el).trigger('refreshEvent',[toast('Refreshing data','warning',4)]);
							//found=true;
							ret = false;
						}
						else if (el.id==='flashmem') {
							$('.refresh-me').removeClass('ui-state-disabled').addClass('ui-state-disabled');
							$('.refresh-fe').removeClass('ui-state-disabled').addClass('ui-state-disabled');
							$('.refresh-fm').removeClass('ui-state-disabled');//now enabled by flashmem itself
							enable_GUI();
							//found=true;
							ret = false;
						}
						else if (el.id==='fileman') {
							$('.refresh-fm').removeClass('ui-state-disabled').addClass('ui-state-disabled');
							$('.refresh-me').removeClass('ui-state-disabled').addClass('ui-state-disabled');
							//$('.refresh-fe').removeClass('ui-state-disabled');//now enabled by flashmem itself
							$(el).trigger('refreshEvent',[toast('Refreshing data','warning',4)]);
							//found=true;
							ret = false;
						}
						return ret;
					});
				},
				beforeLoad: function(event, ui) {
					if (ui.tab.data('loaded')) {
						event.preventDefault();
					}
					else{
						if (navigator.plugins.length>0 && token.length>0) {
							ui.ajaxSettings.headers= {'X-Client-Type':btoa(navigator.plugins[0].filename), 'X-CSRF-Token': token};
							ui.ajaxSettings.method='POST';
						}
						// Ugly hack to insert the loading progress bar gif animation & ensure it is visible
						// Cannot use  CSS for this because base64 images break ssl on ps3 browser & url method does not load quick enough
						var img = new Image();
						img.width=128;
						img.height=15;
						var cstyle = Cookies.get('style');
						img.src = cstyle==='eggplant' ? 'assets/jqueryui/eggplant/images/loading_bar_purple.gif':
								cstyle==='hot-sneaks' ? 'assets/jqueryui/hot-sneaks/images/loading_bar_darkblue.gif':
								cstyle==='redmond' ? 'assets/jqueryui/redmond/images/loading_bar_blue.gif':
								'assets/jqueryui/sunny/images/loading_bar_darkbrown.gif';
						$('.ui-tabs-anchor').addClass('ui-state-disabled');
						ui.panel.html('<div class=\'container-loading-bar\'><table><tbody><tr><td><div align=\'center\' class=\'min-width-200 pad-bottom-10px\'><b>Downloading tool, please wait...</b></div></td></tr><tr><td><div class=\'loading-bar\'></div></td></tr></tbody></table></div>');
						$('.loading-bar').append(img);
						ui.jqXHR.fail(function() {
							if(reloads<3){
								ui.panel.html('<div class=\'container-loading-bar\'><table><tbody><tr><td><div align=\'center\' class=\'min-width-200 pad-bottom-10px\'><b>Downloading error. Attempting to reload tool, please wait...</b></div></td></tr><tr><td><div class=\'loading-bar\'></div></td></tr></tbody></table></div>');
								$( '#tabs' ).tabs( 'load',$( '#tabs' ).tabs( 'option', 'active' ));
							}
							else{
								ui.panel.html('<div class=\'container-loading-bar\'><table><tbody><tr><td><div align=\'center\' class=\'min-width-200 pad-bottom-10px\'><b>Tool could not be downloaded</b></div></td></tr><tr><td><div class=\'loading-error\'></div></td></tr></tbody></table></div>');
								$('.ui-tabs-anchor').removeClass('ui-state-disabled');
							}
						});
						ui.jqXHR.success(function() {
							ui.tab.data( 'loaded', true );
							reloads=0;
							if (ui.ajaxSettings.url.indexOf('memedit.php')>=0) {
								$('.refresh-fm').removeClass('ui-state-disabled').addClass('ui-state-disabled');
								$('.refresh-fe').removeClass('ui-state-disabled').addClass('ui-state-disabled');
								$('.refresh-me').removeClass('ui-state-disabled');
							}
							else if (ui.ajaxSettings.url.indexOf('flashmem.php')>=0) {
								$('.refresh-me').removeClass('ui-state-disabled').addClass('ui-state-disabled');
								$('.refresh-fe').removeClass('ui-state-disabled').addClass('ui-state-disabled');
								$('.refresh-fm').removeClass('ui-state-disabled');
							}
							else if (ui.ajaxSettings.url.indexOf('fileman.php')>=0) {
								$('.refresh-fm').removeClass('ui-state-disabled').addClass('ui-state-disabled');
								$('.refresh-me').removeClass('ui-state-disabled').addClass('ui-state-disabled');
								$('.refresh-fe').removeClass('ui-state-disabled');
							}
							$('.ui-tabs-anchor').removeClass('ui-state-disabled');
						});
						reloads++;
					}
				},
				show: { effect: 'fadeIn', duration: 800, easing:'swing' }
			});
			set_style_from_cookie();
					$('#lpage_prev').button({
				icon: 'ui-icon-seek-prev',
				disabled: true
			});
			$('#lpage_prev').on('click',function(){
				var event = document.createEvent('Event');
				event.initEvent('prevPage', false, false);
				event.page = parseInt($('#lpage_curr').text())-2;
				frames['ifrlog'].window.document.dispatchEvent(event);
				$('#lpage_next').button('enable');
			});
			$('#lpage_next').button({
				icon: 'ui-icon-seek-next',
				disabled: true
			});
			$('#lpage_next').on('click',function(){
				var event = document.createEvent('Event');
				event.initEvent('nextPage', false, false);
				event.page = parseInt($('#lpage_curr').text());
				frames['ifrlog'].window.document.dispatchEvent(event);
				$('#lpage_prev').button('enable');
			});
			window.scrollTo(0,0);
		};
		</script>
		<link type="text/css" rel="stylesheet" href="assets/css/gfont.css">
		<link type="text/css" rel="stylesheet" href="assets/css/fork-awesome.min.css">
	</head>
	<body id="BodyID" class="ui-helper-hidden" style="overflow: hidden;height:auto;visibility:hidden;">
		<div class="preloader ui-helper-hidden"><div class="container-busy-icon"><div class="busy-icon"></div></div></div>
			<div id="title" class="ui-helper-hidden main-title ui-widget-header ui-corner-all">
				<h1>PlayStation 3 Toolset <span class='header-small-text'>by @bguerville</span></h1>
				<h4 id='ps3details' class="ps3-details">Initializing PS3 Toolset v1.1 <span class='header-small-text'>build 003</span><br/>Please Wait</h4>
				<form action="#">
					<select id="themes" >
						<option value="dummy" disabled selected>Change Theme</option>
						<option value="sunny" >Sunny</option>
						<option value="eggplant" disabled>Eggplant</option>
						<option value="hot-sneaks">Hot Sneaks</option>
						<option value="redmond">Redmond</option>
					</select>
				</form>
			</div>
			<div id="tabs" class='ui-helper-hidden main-tabs ' style='height:780px;min-height:780px;'>
				<ul>
					<li><a href='#toolset'><i class="fa fa-home fa-fw"></i> Home</a></li>
					<li><a href='memedit.php'><i class="fa fa-table fa-fw"></i> Memory Editor<span title='Refresh Memory Editor Tab' class='refresh fa fa-refresh ui-state-disabled refresh-me pointer tab-icon'></span></a></li>
					<li><a href='flashmem.php'><i class="fa fa-microchip fa-fw"></i> Flash Memory Manager<span title='Refresh Flash Memory Manager Tab' class='refresh fa fa-refresh ui-state-disabled refresh-fm pointer tab-icon'></span></a></li>
					<li><a href='fileman.php'><i class="fa fa-table fa-hdd-o"></i> File Manager (soon)<span title='Refresh File Manager Tab' class='refresh fa fa-refresh ui-state-disabled refresh-fe pointer tab-icon'></span></a></li>
					<li><a href='#tblog'><i class="fa fa-list-alt fa-fw"></i> Logs</a></li>
				</ul>
				<div id="toolset">
					<h2 align='right' class='tab-header'>PS3 Toolset <span class='header-tiny-text'>v1.1.003</span></h2>
					<div class='intro-table'>
						<div class='box-table' style="max-height:620px;min-height:600px;height:620px;">
							<div class='box-cell-30 '>
								<table class="window-250">
									<tbody class="window-250">
										<tr class="window-header ui-widget-header">
											<th class="logoptions window-header ui-widget-header">
												<div class="nopad">
													<span class="fa-stack fa-fw" style="font-size:12px;">
														<i class="fa fa-square-o fa-stack-2x fa-fw"></i>
														<i class="fa fa-commenting-o fa-stack-1x fa-fw" style="font-size:8px;"></i>
													</span>
													<span class='top2px baloo-header'> Welcome</span>
												</div>
											</th>
										</tr>
										<tr class="logoptions window-content-top ui-widget-content">
											<td id='intr' align="justify" class="window-content-top">
												<div class='sizer'>
													<i class="fa fa-border fa-quote-left fa-pull-left fa-fw" style="font-size:10px;"></i>
													The PS3 Toolset is a repository project for tools built upon my latest ps3 exploitation framework v4.1.<br/>
													New tools & features should be added to this repository with time.<br/>
													I hope you enjoy using them as much as I enjoy making them.
													<i class="fa fa-border fa-quote-right fa-pull-right fa-fw" style="font-size:10px;"></i>
													<br/>
													<div class='pad-sig align-right'>@bguerville</div>
												</div>
											</td>
										</tr>
										<tr class="pl window-bottom-small">
											<td align="justify" class="window-bottom-small">
												<div class='sizer height-5px'>XXX</div>
											</td>
										</tr>
									</tbody>
								</table>
								<br/><br/><br/>
								<table class="window-250">
									<tbody class="window-250">
										<tr class="window-header ui-widget-header">
											<th class="logoptions window-header ui-widget-header">
												<div class="nopad">
													<span class="fa-stack fa-fw" style="font-size:12px;">
														<i class="fa fa-square-o fa-stack-2x fa-fw"></i>
														<i class="fa fa-exclamation-triangle fa-stack-1x fa-fw" style="font-size:8px;"></i>
													</span>
													<span class='top2px baloo-header'> Privacy</span>
												</div>
											</th>
										</tr>
										<tr class="logoptions window-content-top ui-widget-content">
											<td id='security' align="justify" class="window-content-top">
												<div class='sizer'>
													This website does not collect or store any information of personal or technical nature related to you or your console.<br/>
													No data from your console ever gets transmitted to our web server when using the PS3 Toolset tools, all operations are conducted locally.<br/>
													Cookies are used locally on the ps3 for persisting a handful of PS3 Toolset variables from one session to the next.
												</div>
											</td>
										</tr>
										<tr class="pl window-bottom-small">
											<td align="justify" class="window-bottom-small">
												<div class='sizer height-5px'>XXX</div>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
							<div class='width-600 box-cell-70' >
								<div id="intro-accordion">
								<h3> Latest News</h3>
								<div>
									<div align='left' class='wrap-don'>
									<br/><br/>
										05/06/2021 Update v1.1.003
										<ul class="fa-ul">
											<li><i class="fa-li fa fa-chevron-circle-right"></i>Added support for 4.88 CEX<br/></li>
											<li><i class="fa-li fa fa-chevron-circle-right"></i>Flash NC Exploit update v3.0<br/></li>
											<li><i class="fa-li fa fa-chevron-circle-right"></i>FMM update v1.3.1<br/></li>
											<li><i class="fa-li fa fa-chevron-circle-right"></i>JS Framework update v4.2<br/></li>
										</ul>
									</div>
									<br/>
									<div align='right' class='wrap-don'>
										<i class="fa fa-border fa-quote-left fa-fw" style="font-size:8px;"></i>
										<span style="font-size:11px;font-style:italic;">Just a minor release!</span>
										<i class="fa fa-border fa-quote-right fa-fw" style="padding-left:5px;font-size:8px;"></i>
									</div>
									<br/>
								</div>
								<h3> General Information</h3>
								<div>
									<div align='left' class='wrap-don'>
										<ul class="fa-ul">
											<li><i class="fa-li fa fa-chevron-circle-right"></i>You are free to use the tools in this project at your own risk.
												Keep in mind that no official support is provided, if you experience any kind of problem & find yourself in need of help, I strongly recommend that you turn to the <a href="https://www.psx-place.com/forums/PS3Xploit/" title="https://www.psx-place.com/forums/PS3Xploit/">PS3Xploit sub-forum on psx-place.com</a> for support & guidance..</li>
											<li><i class="fa-li fa fa-chevron-circle-right"></i>The Flash Player 9 browser plugin must be enabled to use the PS3 Toolset.<br/>
											If ever you disabled it permanently in the current user profile, you may need to log in as another user or create a new profile to be able to use any of the tools in this project.</li>
											<li><i class="fa-li fa fa-chevron-circle-right"></i>You can enable Flash permanently by checking the "Do not display again" checkbox in the plugin confirmation screen before accepting to load the Flash plugin.</li>
											<li><i class="fa-li fa fa-chevron-circle-right"></i>It is highly recommended that you adjust the console's System Time settings properly to avoid any time related issues with the browser and/or the Flash Player plugin.</li>
											<li><i class="fa-li fa fa-chevron-circle-right"></i>To avoid potential crashes, you should never attempt to close the browser while toolset operations are in progress, especially when the browser exit confirmation setting is turned off.</li>
										</ul>
									</div>
								</div>
								<h3> Minimum Requirements</h3>
								<div>
									<div align='left' class='wrap-don'>
										<ul class="fa-ul" style="line-height:22px;">
											<li><i class="fa-li fa fa-chevron-circle-right" style="line-height:18px;"></i>PS3 Browser Flash Player 9 Plugin enabled<br/></li>
											<li><i class="fa-li fa fa-chevron-circle-right" style="line-height:18px;"></i>PS3 Browser Javascript enabled<br/></li>
											<li><i class="fa-li fa fa-chevron-circle-right" style="line-height:18px;"></i>PS3 Browser Cookies enabled<br/></li>
											<li><i class="fa-li fa fa-chevron-circle-right" style="line-height:18px;"></i>PS3 Firmware: 4.80/4.81/4.82/4.83/4.84/4.85/4.86/4.87/4.88<br/></li>
											<li><i class="fa-li fa fa-chevron-circle-right" style="line-height:18px;"></i>PS3 Firmware Type: OFW/HFW/MFW/CFW<br/></li>
											<li><i class="fa-li fa fa-chevron-circle-right" style="line-height:18px;"></i>PS3 Firmware mode: CEX/DEX<br/></li>
											<li><i class="fa-li fa fa-chevron-circle-right" style="line-height:18px;"></i>PS3 System Time accurately set<br/></li>
										</ul>
									</div>
								</div>
								<h3> Acknowledgements</h3>
								<div>
									<div class='wrap-don'>
										<p>My warmest thanks to Jason, for his friendship & support of course, but in the context of this project, also for testing my work all year round whenever needed.<br/></p>
										<br/>
										<p>The PS3 Toolset & its GUI were built in native js upon various open source js libraries including jQuery, jQueryUI, bigInteger, jstree, mCustomScrollbar, js-logger, js-cookie, sjcl, switchButton & toastmessage as well as the Fork Awesome CSS icon library.<br/>Thanks to all the coders involved in the various projects.</p>
										<br/>
										<p>Thanks to ps3/vita scene hackers, developers, forum creators and psdevwiki contributors, all essential in bringing us to this point.</p>
									</div>
								</div>
								<h3> Help & Donations</h3>
								<div>
									<div class='wrap-don'>
										On behalf of the PS3Xploit team & users, I would like to convey our sincere thanks to all Paypal donators for their support to date, their contributions so far have allowed the team to cover the ever growing maintenance costs.<br/>
										We need your continued support if we are to keep providing the services we offer both free & ad-free.
										If you wish to help us, consider a donation via Paypal at team@ps3xploit.net or in BTC at either of the addresses below.<br/><br/>
										<div class='container-qr'>
											<div class='box-table-180'>
												<div class='box-row'>
													<div class='box-cell-33'>
														<img class="qr-size" src='assets/images/qr-legacy-P2PKH.png' title='1CWjJrrV5LxeFbSZAtcGXFgJ9wepFdZAqT'>
													</div>
													<div class='box-cell-33'>
														<img class="qr-size" src='assets/images/qr-native-segwit-BECH32.png' title='bc1qe8maczwynmkj3vkhz3p28kxtr0lqdefvkgrq72'>
													</div>
													<div class='box-cell-33'>
														<img class="qr-size" src='assets/images/qr-PayNyms.png' title='PM8TJKzzUZAzj3hdVezaMVXN62H6fFPPoRMZ2GPfE5jQx89RRaD9xS39ft2HE5QYGJ4qsxk7eCm6EqtFEnXxM8NuWbgW9uYXFYw4gcfs5XjTkyBp3JHc'>
													</div>
												</div>
												<div class='box-row'>
													<div class='box-cell-33 pad-left-3pct'>
														Legacy P2PKH
													</div>
													<div class='box-cell-33 pad-left-4pct'>
														Segwit BECH32
													</div>
													<div class='box-cell-33 pad-left-3pct'>
														PayNyms
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id='tblog' class="tb-log" style="max-height:90%;">
				<h2 align='right'  class='tab-header'>Logs <span class='header-tiny-text'>v1.1</span></h2>
				<div class="max-height-620">
					<table class="window">
						<tbody class=''>
							<tr class="window-header">
								<th class="logoptions window-header ui-widget-header">
									<div class="dir-table-auto" style="max-height:25px;height:25px;font-size:12px;">
										<span class='min-width-410 dir-left' style="min-width:600px;width:600px;padding-left:0;">
											<span class='sizer'>
												<input type='checkbox' id='ilog' name='ilog' checked />
												<label id="lilog" for="ilog" title="Logs" class="logbtn">Logs</label>
												<input type='checkbox' class="ui-widget gui-item" id='iwarn' name='iwarn' checked />
												<label id="liwarn" for="iwarn" title="Warnings" class="logbtn">Warnings</label>
												<input type='checkbox' id='ierror' name='ierror' class='gui-item' checked  />
												<label id="lierror" for="ierror" title="Errors" class="logbtn">Errors</label>
												<input type='checkbox' id='idbg' name='idbg' class='gui-item' />
												<label id="lidbg" for="idbg" title="Toolset Debugger logs" class="logbtn">Debug Messages</label>
												<span style="padding-left:20px;font-size:8px;">
													<button id="lpage_prev" class='gui-item'  style="max-width:40px;font-size:8px;margin-bottom:0.2em;"></button>
													<span style="padding-left:5px;font-size:10px;"> Log page: </span>
													<span id="lpage_curr"> 1</span>
													<span>/</span>
													<span id="lpage_ntotal"  style="padding-right:5px;">1 </span>
													<button id="lpage_next" class='gui-item'  style="max-width:40px;font-size:8px;margin-bottom:0.2em;"></button>
												</span>
												<span style="padding-left:20px;">
													<input type='checkbox' id='inet' name='inet' class='gui-item' />
													<label for="inet" title="Toolset Debugger logs over UDP" >UDP Broadcast</label>
													<label class='labport' for="port_txtbox" style="padding-left:5px;"> Port: </label>
													<input type='text' id='port_txtbox' name='port_txtbox' class='gui-item port ui-corner-all' value='18194' />
												</span>
											</span>
										</span>
									</div>
								</th>
							</tr>
							<tr class='max-height-620 logoptions window-content-top ui-widget-content'>
								<td align='justify' class='window-content-top ui-widget-content'>
								<iframe id='ifrlog' name='ifrlog'  frameborder='0'  scrolling='no' src='log.php?tk=UNVPUxcpvBNmTOX0Hm2rl3DzxrL0cnK7qowmg8Z2lkw7' class='' style='max-width:100%;width:100%;max-height:600px;height:600px;display:block;border-style:none;border-width:0;'>
								</iframe>
								</td>
							</tr>
							<tr class='pl window-bottom-small'>
								<td align='justify' class='window-bottom-small'>
									<div class='sizer height-5px'>XXX</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<br/>
		<div id='dg-confirm' class='ui-helper-hidden' title=''>
			<p><span class='ui-icon ui-icon-alert'></span><span id='dg-text' class='dg-text'></span></p>
		</div>
		<div class='ui-helper-hidden-accessible' >
			<div id="explt" class='ui-helper-hidden' ></div>
			<div id="pf" class='ui-helper-hidden' ></div>
			<div id="FPX2" class='ui-helper-hidden' ></div>
			<div id="TSound" class='ui-helper-hidden' ></div>
		</div>
	</body>
</html>
