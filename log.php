
				<html class='ui-widget-content' style='border-width:0;'>
				<head>
					<link type='text/css' rel='stylesheet' href='assets/css/fork-awesome.min.css' >
					<link type='text/css' rel='stylesheet' href='assets/css/gfont.css'>
				</head>
				<body  class='ui-widget-content' style='border-width:0;'>
					<div id='sflog'  class='ui-widget-content' style='border-width:0;'>
						<div id='txtlog' class='txt-log' style='border-width:0;'></div>
					</div>
					<script>
						function load_logs(){
							document.removeEventListener('loadLog',load_logs);
							if(typeof(jQuery)=='undefined' && typeof(parent.getLibData)=='function'){
								var libs = parent.getLibData();
								for(var i=0;i<libs.length;i++){
									if(libs[i].library==='jquery' || libs[i].library==='mscb'){
										if(libs[i].data===null){
											alert('null data');
											setTimeout(load_logs,1000);
											return;
										}
										eval(libs[i].data);
									}
								}
							}
							jQuery.each(parent.getCssData(),function(j,css){
								if(css.library!='sunny' && css.library!='eggplant' && css.library!='redmond' && css.library!='hot-sneaks'){
									$('head').append(css.data);
								}
							});
							var tx = document.getElementById('txtlog');
							var flg = jQuery('#sflog');
							var curr_css = 'eggplant';
							var clog=true;
							var cwarn=true;
							var cerr=true;
							var cdeb=false;
							var pages = [''];
							//var maxbuf = 0x100000;//1Mb
							var maxbuf = 0x20000;//128kb
							var rt_page=true;
							function switch_style(e){
								curr_css = e.style;
								jQuery('head').find('style').remove();
								jQuery.each(parent.getCssData(),function(j,css){
									if(css.library===curr_css || (css.library!='sunny' && css.library!='eggplant' && css.library!='redmond' && css.library!='hot-sneaks')){
									jQuery('head').append(css.data);
								}
								});
								refresh_scrollbar();
							}
							function clean_log(){
								tx.innerHTML='';
							}
							function destroy_sb(){
								flg.mCustomScrollbar('destroy');
							}
							function refresh_scrollbar(){
								destroy_sb();
								flg.mCustomScrollbar({
									theme: (curr_css==='eggplant') ? 'light-thick' : 'dark-thick',
									advanced:{
										updateOnContentResize: true,
										updateOnImageLoad: true
									}
								});
							}
							function filter_data(p_class,checked){
								var li = jQuery(document.body).find('.'+p_class).removeClass('ui-helper-hidden');
								if(checked!==true){
									li.addClass('ui-helper-hidden');
								}
							}
							function toggle_log(e){
								if(e && typeof e.toggle === 'boolean'){
									filter_data('log-info',e.toggle);
									clog = e.toggle===true ? true: false;
								}
							}
							function toggle_warn(e){
								if(e && typeof e.toggle === 'boolean'){
									filter_data('log-warning',e.toggle);
									cwarn = e.toggle===true ? true: false;
								}
							}
							function toggle_error(e){
								if(e && typeof e.toggle === 'boolean'){
									filter_data('log-error',e.toggle);
									cerr = e.toggle===true ? true: false;
									
								}
							}
							function toggle_dbg(e){
								if(e && typeof e.toggle === 'boolean'){
									filter_data('log-debug',e.toggle);
									cdeb = e.toggle===true ? true: false;
								}
							}
							var show_page = function(idx){
								function scroll(){
									flg.mCustomScrollbar('scrollTo','bottom',{
										timeout:50,
										scrollInertia:3000,
										scrollEasing:'easeOut'
									});
								}
								function update(){
									tx.innerHTML = pages[idx];
									scroll();
									parent.updateCurrentLog(idx+1);
									filter_all();
								}
								if(pages.length>1 && idx>=0 && idx<pages.length-1){
									update();
									rt_page=false;
								}
								else if(pages.length>1 && idx===pages.length-1){
									update();
									rt_page=true;
								}
								else if(idx===pages.length-1){
									update();
									rt_page=true;
								}
							}
							function add_log(){
								if(rt_page){
									show_page(pages.length-1);
								}
							}
							function queue_log(e){
								if(e && typeof e.message === 'string' && e.message.length>0){
									if(pages[pages.length-1].length+e.message.length<maxbuf){
										pages[pages.length-1]+=e.message;
									}
									else{
										pages.push(e.message);
										parent.updateTotalLogs(pages.length);
									}
									if(e.immediate){add_log();}
								}
							}
							function filter_all(){
								filter_data('log-info',clog);
								filter_data('log-warning',cwarn);
								filter_data('log-error',cerr);
								filter_data('log-debug',cdeb);
							}
							function first_page(e){
								show_page(0);
							}
							function last_page(e){
								show_page(pages.length-1);
							}
							function next_page(e){
								show_page(e.page);
							}
							function prev_page(e){
								show_page(e.page);
							}
							document.addEventListener('firstPage',first_page);
							document.addEventListener('lastPage',last_page);
							document.addEventListener('nextPage',next_page);
							document.addEventListener('prevPage',prev_page);
							document.addEventListener('showLog',add_log);
							document.addEventListener('addLog',queue_log);
							document.addEventListener('cleanLogs',clean_log);
							document.addEventListener('updateScroll',refresh_scrollbar);
							document.addEventListener('toggleLogs',toggle_log);
							document.addEventListener('toggleErrors',toggle_error);
							document.addEventListener('toggleWarnings',toggle_warn);
							document.addEventListener('toggleDebugs',toggle_dbg);
							document.addEventListener('destroyScrollbar',destroy_sb);
							document.addEventListener('switchStyle',switch_style);

							window.onbeforeunload = function() {
								document.removeEventListener('firstPage',first_page);
								document.removeEventListener('lastPage',last_page);
								document.removeEventListener('nextPage',next_page);
								document.removeEventListener('prevPage',prev_page);
								document.removeEventListener('showLog',add_log);
								document.removeEventListener('updateScroll',refresh_scrollbar);
								document.removeEventListener('toggleLogs',toggle_log);
								document.removeEventListener('toggleErrors',toggle_error);
								document.removeEventListener('toggleWarnings',toggle_warn);
								document.removeEventListener('toggleDebugs',toggle_dbg);
								document.removeEventListener('destroyScrollbar',destroy_sb);
								document.removeEventListener('switchStyle',switch_style);
								document.removeEventListener('cleanLogs',clean_log);
								document.removeEventListener('addLog',queue_log);
							};
							}
						window.onload = function(){
							document.addEventListener('loadLog',load_logs);
						};
					</script>
				</body>
			</html>
			