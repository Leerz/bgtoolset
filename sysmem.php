
				<style>
				.xreg-filtered{
					color: black !important;
				}
				</style>
				<div id='sysmem'>
					<iframe name='dlframe' id='dlframe' src='blank.php' class='dl-frame ui-helper-hidden'></iframe>
					<h2 align='right' class='tab-header'>System Manager <span class='header-tiny-text'>v1.3.1</span></h2>
					<div id='treecontainer' class='fm-container'>
						<table id='fmbox' class='window' style='min-height:600px !important;height:600px !important;'>
							<tbody class=''>
								<tr class='window-header ui-widget-header'>
									<th class='logoptions window-header ui-widget-header '><div class='dir-table'><span class='dir-left header-normal-text'>CFW Compatible PS3: <span class='fmm-compat'><span id='cfwcompat'></span></span></span><span class='dir-center header-normal-text'></span><span id='spanmode' class='dir-right-fixed' title='Disabling SM Strict Mode is very risky.Patching checks & restrictions are disabled when SM Strict Mode is off.Use at your own risk!!!'><div class='switch-wrapper pointer' tabindex='0'><input type='checkbox' name='mode' id='admode' value='true' checked='true' ></div></span></div></th>
								</tr>
								<tr class='window-content-top ui-widget-content'>
									<td align='justify' class='window-content-top ui-widget-content'>
										<div id='fTree' class='fm-tree ui-widget-content with-y-scrollbar' style='min-height:550px !important;height:550px !important;'></div>
									</td>
								</tr>
								<tr class='pl window-bottom-small'>
									<td align='justify' class='window-bottom-small'>
										<div class='sizer height-5px'>XXX</div>
									</td>
								</tr>
							</tbody>
						</table>
						<br>
					</div>
				</div>
				<script>
				jQuery('.refresh-fm').removeClass('ui-state-disabled').addClass('ui-state-disabled');
				var ft1 =null;
				dl_object=null;
				if(!helper.sm){
					helper.sm = new sysmem();
				}
				if(!helper.worker['fmm']){
					helper.worker['fmm'] = new workerThread('BGTOOLSET_WKR_FMM');
				}
				function initPatchData(){
					helper.deletePatchData = function(){
						var fpstr = helper.heap.store(helper.fm_tmpfile_path,true);
						var fpstat = helper.heap.store(0x80);
						if(stdc.stat(fpstr,fpstat)===0){
							if(stdc.unlink(fpstr)===0){
								Logger.info('SM: Deleted temporary patch file '+helper.fm_tmpfile_path);
							}
							else{
								Logger.error('SM: Temporary patch file '+helper.fm_tmpfile_path+' deletion error');
							}
						}
						helper.heap.free([fpstr,fpstat]);
					};
					helper.existPatchData = function(){
						var fpstr = helper.heap.store(helper.fm_tmpfile_path,true);
						var fpstat = helper.heap.store(0x80);
						var exists = false;
						if(stdc.stat(fpstr,fpstat)===0){
							exists = true;
						}
						helper.heap.free([fpstr,fpstat]);
						return exists;
					};
					helper.savePatchData=function(){
						if(jQuery('#fTree').jstree(true).get_node('flashbk').children.length>0){
							if(helper.sha256_loadedros === getSHA256hash(helper.rosBuffer.offset+0x30, helper.patchfile_size)){
								var fp = new fileObject(helper.fm_tmpfile_path,helper.fs_flag_create_rw);
								if(fp.save({'offset':helper.rosBuffer.offset+0x30,'size':helper.rosBuffer.size-0x30},helper.patchfile_size,null,null)!==0){
									Logger.warn('Patch Data could not be saved to file');
								}
								fp.close();
							}
						}
					};
					helper.loadPatchData=function(){
						if(jQuery('#fTree').jstree(true).get_node('flashbk').children.length>0){
							if(helper.sha256_loadedros !== getSHA256hash(helper.rosBuffer.offset+0x30, helper.patchfile_size)){
								var fp = new fileObject(helper.fm_tmpfile_path,helper.fs_flag_readonly);
								if(fp.size()===helper.patchfile_size){
									if(fp.load(helper.patchfile_size,{'offset':helper.rosBuffer.offset+0x30,'size':helper.rosBuffer.size-0x30},null,null)!==0){
										Logger.error('Patch Data could not be restored from file');
										removePatch();
									}
									else if(helper.sha256_loadedros !== getSHA256hash(helper.rosBuffer.offset+0x30, helper.patchfile_size)){
										Logger.error('Patch Data could not be restored from file');
										removePatch();	
									}
									else{
										helper.deletePatchData();
									}
									fp.close();
								}
								else{
									Logger.error('Patch Data could not be restored from file');
									removePatch();
								}
							}
							else{
								helper.deletePatchData();
							}
						}
						else{
							helper.deletePatchData();
						}
					};
					helper.deletePatchData();
				}
				var removePatch = function(){
					var jQftree = jQuery('#fTree').jstree(true);
					var _node = jQftree.get_node('flashbk');
					var children = _node ? _node.children : [];
					if(children.length>0){
						jQftree.delete_node(children);
					}
					helper.sha256_loadedros='';
					helper.rosBuffer={'offset':0,'size':0};
					if(dl_object){dl_object=null;}
					if(helper.deletePatchData){
						helper.deletePatchData();
					}
					//buf_po={'offset':0,'size':0};
				};
				//function updatePD(o,st){
				//	pbfm1.updateProgressDialog(o,st);
				//}
				//function updateBuffer(b){
					//ldiag.addPatchInfo(b);
				//	helper.rosBuffer=b;
				//}
				function prepPatchFile(buf_po){
					var _nor = so.is_nor();
					helper.memory.upokes(buf_po.offset,helper.patch_ros_fragment_start);
					if(!_nor){
						helper.memory.upokes(buf_po.offset,getActiveNandROS(so));
					}
					helper.memory.upokes(buf_po.offset+helper.patchfile_size+0x30,_nor ? helper.patch_ros_fragment_end1:helper.patch_ros_fragment_end2);
				}
				function validatePatchFile(filename,silent){
					//alert('validatePatchFile');
					Logger.info('getSHA256hash 0x'+(helper.rosBuffer.offset+0x30).toString(16));
					helper.sha256_loadedros = getSHA256hash(helper.rosBuffer.offset+0x30, helper.patchfile_size);
					if(dl_object){dl_object.sha256 = helper.sha256_loadedros;}
					if(!silent){pbfm1.ulog('SHA256 Extraction Complete');}
					Logger.info('Patch File '+filename+' SHA256 checksum: '+helper.sha256_loadedros);
					if(!silent){pbfm1.ulog('Patch validation operations complete');}
					if(helper.sha256_loadedros!==helper.nofsm_hash){
						if(dl_object){toast('Patch Download Error','warning',5);}
						Logger.warn('Custom patch file detected.');
						//alert('Custom patch file detected');
						return 1;
					}
					else{
						Logger.info('official patch file detected.');
						//alert('official patch file detected');
						return 0;
					}
				}
				function updateValidationGUI(start,filename){
					var jQftree = jQuery('#fTree').jstree(true);
					if(!jQftree.is_disabled('flashbk')){
						jQftree.create_node('flashbk',{'id' : 'rosbk', 'type' : 'ros', 'text' : 'ROS' });
						jQftree.create_node('rosbk',{'id' : 'infobk', 'type' : 'info', 'text' : 'SHA256: '+helper.sha256_loadedros });
						jQftree.open_node('rosbk');
						jQftree.open_node('flashbk');
					}
					setTimeout(function(){
						helper.sp.playOK();
						pbfm1.updateProgressDialog({'dlabel':'Idle','glabel':'Patch File \''+filename+'\' loaded & validated','dvalue':100,'gvalue':100,'istatus':'success-image'},start);
					},250);
				}
				function updateNoValidationGUI(start,filename){
					helper.sp.playNG();
					pbfm1.updateProgressDialog({'glabel':'Loading Operations failed','dlabel':'File validation error','dvalue':100,'gvalue':100,'istatus':'error-image'},start);
					Logger.info('Invalid Patch File '+filename);
				}
				function xRegTableCleanup(){
					Logger.info('XRegTable cleanup');
					var nfo = jQuery('#xr_kinfo');
					if(nfo.html().length>nfo.text().length){
						//jQuery('.xreg-value').off('focusin focusout change');
						jQuery('tr.xreg-setting').contextMenu('destroy');
						jQuery('div.xr-list-container').contextMenu('destroy');
					}
					var ell = jQuery('i.ellipsis');
					if(ell.length>0){
						ell.contextMenu('destroy');
					}
					jQuery('#xreg_table').mCustomScrollbar('destroy');
					helper.xregistry.xdestroyList(xRegSettings.xlist.list);
					helper.xregistry.xclose();
					xRegSettings = null;
				}
				function dl_cancel(){
					helper.swf.cancelDownload();
					dl_object=null;
					helper.sp.playNG();
				}
				function processXRegistryTable(){
					var xkfilter='*',xdfilter='*';
					if(xRegSettings.active.length===0){
						jQuery('#xreg_table').html('<div>xRegistry Data Extraction Errors</div>');
					}
					else{
						jQuery.each(xRegSettings.inactive,function(k,ktem){ // to add the list of keys to display for each directory
							if(ktem.setting.indexOf('/setting/system/hddSerial')<0 || ktem.setting.indexOf('/setting/np/env')<0 || ktem.setting.indexOf('/setting/wboard/baseUri')<0){
								Logger.info('xRegSettings inactive: '+ktem.setting);
							}
						});
						var tjson = [{ 'id' : 'xtree_setting', 'parent' : '#', 'text' : 'setting'}];
						jQuery.each(xRegSettings.directory,function(i,item){ // building tree with directory array
							jQuery.each(xRegSettings.directory,function(j,jtem){
								if(item.setting+jtem.setting.substr(jtem.setting.lastIndexOf('/')) === jtem.setting){
									var a_attr = {'data-xrf':[],'title':jtem.setting};
									jQuery.each(xRegSettings.active,function(k,ktem){ // to add the list of keys to display for each directory
										if(jtem.setting+ktem.setting.substr(ktem.setting.lastIndexOf('/')) === ktem.setting){
											a_attr['data-xrf'].push({'setting':ktem.setting,'pointer':ktem.pointer,'active':true});
										}
									});
									jQuery.each(xRegSettings.inactive,function(k,ktem){ // to add the list of keys to display for each directory
										if(jtem.setting+ktem.setting.substr(ktem.setting.lastIndexOf('/')) === ktem.setting){
											a_attr['data-xrf'].push({'setting':ktem.setting,'pointer':ktem.pointer,'active':false});
										}
									});
									tjson.push({ 'id' : 'xtree'+jtem.setting.replace(/([/])/g,'_'), 'icon' : 'jstree-folder', 'parent' : 'xtree'+item.setting.replace(/([/])/g,'_'), 'text' : jtem.setting.substr(jtem.setting.lastIndexOf('/')+1), 'a_attr' : a_attr});
								}
							});
						});
						var xr_asc = true;
						var jst_xr = jQuery('#xr_ktree');
						jst_xr.jstree({
							'core' : {
								'data' : tjson,
								'multiple' : false
							},
							'plugins' : [ 'unique', 'search', 'sort', 'changed' ],//, 'state'
							'themes':{
								'dots': true,
								'icons': true
							},
							'sort' : function(a, b) {
								if(xr_asc){
									return (this.get_node(a).text > this.get_node(b).text) ? 1 : -1;
								}
								else{
									return (this.get_node(b).text > this.get_node(a).text) ? 1 : -1;
								}
							}
						});
						
						jst_xr.on('search.jstree', function (e, data) {
							if(data.nodes.length===0){
								alert('No matches for '+data.str);
							}
							else{
								if(!xdfilter.length && xdfilter.ellipsis){
									jQuery('#'+xdfilter.ellipsis).after('<span  style=\'display:inline-block;height:12px;\'><i class=\'fa fa-search xd-filter-icon\' title=\'Filter Pattern: \''+data.str+'\'\' aria-hidden=\'true\' style=\'font-size:11px;padding-left:10px;\'></i><span>');
									jQuery('.xd-filter-icon').on('click',function(e){
										var xdfi = jQuery('.xd-filter-icon');
										xdfi.off('click');
										data.instance.clear_search();
										xdfilter='*';
										xdfi.remove();
									});
								}
								jQuery.each(data.res,function(ri,ritem){
									jQuery.each(data.instance.get_node(ritem).children_d, function(i,item){
										console.log(item);
										data.instance.show_node(item,false);
									});
								});
							}
							var nd = jQuery('#currentxnode').val();
							try{
								if(!data.instance.is_hidden(nd)){
									data.instance.select_node(nd);
								}
								else{
									data.instance.deselect_all();
									jQuery('#xr_kinfo').html('Select a setting folder in the tree to display keys');
								}
							}
							catch(e){
								data.instance.deselect_all();
								jQuery('#xr_kinfo').html('Select a setting folder in the tree to display keys');
							}
							
						});
						
						function redraw_ellipsis(){
							jQuery.each(jQuery('#xreg_table').find('a.jstree-anchor'), function(_idx,_el){
								_el=jQuery(_el);
								if(!_el.next().hasClass('fa-ellipsis-v')){
									_el.after('<i id=\'ellipsis_icon_'+_idx.toString()+'\' class=\'fa fa-ellipsis-v ellipsis\' aria-hidden=\'true\' style=\'font-size:14px;padding:6px 5px 0 5px;\'></i>');
								}
								return false; // add ellipsis context menu to first tree item only
							});
						}
						jst_xr.on('clear_search.jstree', function (e, data) {
							console.log('clear_search.jstree');
							console.log(xdfilter);
							//xdfilter='*';
						});
						jst_xr.on('redraw.jstree', function (e, data) {
							redraw_ellipsis();
						});
						jst_xr.on('after_open.jstree', function (e, data) {
							//alert('after_open.jstree');
							redraw_ellipsis();
						});
						jst_xr.on('ready.jstree', function (e, data) {
							alert('ready.jstree');
							redraw_ellipsis();
							// jQuery('.scb-xr1').mCustomScrollbar({
								// axis:'xy',
								// theme: (Cookies.get('style')==='eggplant') ? 'light-thick' : 'dark-thick',
								// //theme: 'light-thick',
								// advanced:{
									// updateOnContentResize: true,
									// updateOnImageLoad: true
								// },
								// keyboard: {enable:false},
								// mouseWheel: {enable:false}
							// });
							jQuery('.scb-xr2').mCustomScrollbar({
								axis:'y',
								theme: (Cookies.get('style')==='eggplant') ? 'light-thick' : 'dark-thick',
								//theme: 'light-thick',
								advanced:{
									updateOnContentResize: true,
									updateOnImageLoad: true
								},
								keyboard: {enable:false},
								mouseWheel: {enable:false}
							});
						});
						jst_xr.on('select_node.jstree', function (e, data) {
							//alert(data.node.id);
							jQuery('.preloader-diag').removeClass('ui-helper-hidden');
							jQuery('#currentxnode').val(data.node.id);
							setTimeout(function() {
								var htbl = '<div style=\'height:100%;min-width:355px;width:100%;border:0;\'>No xRegistry keys in '+data.node.a_attr['title']+'</div>';
								//show the values for keys found in a_attr
								if(data.node.a_attr['data-xrf'] && data.node.a_attr['data-xrf'].length>0){
									htbl = '<div class=\'xr-list-container\' style=\'height:100%;border:0;\'><table class=\'xr-list\' style=\'max-width:345px;width:100%;border:0;\'><thead class=\'ui-widget-header\' style=\'height:20px;border:0;\'><tr class=\'\'><th class=\'headerSortDown\' style=\'height:20px !important;max-height:20px !important;min-height:20px !important;width:40%!important;\'>Key</th><th class=\'headerSortDown\' style=\'height:20px !important;max-height:20px !important;min-height:20px !important;width:100px!important;\'>Type</th><th class=\'\' style=\'height:20px !important;max-height:20px !important;min-height:20px !important;width:50%!important;\'>Value</th></tr></thead><tbody style=\'padding-top:4px;padding-bottom:4px;\'>';
									jQuery.each(data.node.a_attr['data-xrf'],function(xri,xritem){
										if(!xritem.pointer || xritem.pointer<0x20000000 || xritem.pointer>0x2FFFFFFC){
											alert('Error invalid pointer');
											return true;
										}
										var obj = helper.xregistry.xget(xritem);
										//use helper.xregistry to get the value & the type of the key (0:bool, 1: integer, 2: string)
										var _tr = 'xstr_'+xri.toString();
										if(obj){ 
											if(obj.error===0){
												var valdisp = obj.type===0 ? '0x'+obj.value.toString(16):obj.value;
												var _disabled = xritem.active ? '' : ' ui-state-disabled';
												htbl += '<tr id=\''+_tr+'\' style=\'border:1px;height:20px !important;max-height:20px !important;\' class=\'xreg-setting ui-widget-content no-background-image\' title=\''+xritem.setting+'\' data-xs=\'{"setting":"'+xritem.setting+'","value":"0x'+obj.value.toString(16)+'","type":"'+obj.type+'","row":"'+_tr+'","id":"'+xritem.setting.replace(/([/])/g,'_')+'"}\'><td align=\'justify\' class=\'ui-widget-content no-background-image\' style=\'border:0;padding:5px;height:20px;max-height:20px;max-width:50%;width:50%;text-align:left!important;\'>'+xritem.setting.substr(xritem.setting.lastIndexOf('/')+1)+'</td><td align=\'justify\' class=\'ui-widget-content no-background-image\' style=\'border:0;padding:5px;max-width:100px;width:100px;height:20px;max-height:20px;text-align:center!important;\'>'+obj.type+'</td><td align=\'justify\' class=\'ui-widget-content no-background-image\'  style=\'border:0;padding:1px 5px 5px 5px;height:20px;max-height:20px;max-width:100%;width:100%;text-align:right!important;\'><input id=\''+xritem.setting.replace(/([/])/g,'_')+'\' class=\'ui-corner-all xr-value'+_disabled+'\' type=\'text\' value=\''+valdisp+'\' readonly/></td></tr>';
											}
											else{
												htbl += '<tr id=\''+_tr+'\' style=\'border:1px;height:20px !important;max-height:20px !important;\' class=\'ui-widget-content no-background-image\' title=\''+xritem.setting+'\'><td align=\'justify\' class=\'ui-widget-content no-background-image\' style=\'border:0;padding:5px;height:20px;max-height:20px;max-width:50%;width:50%;text-align:left!important;\'>'+xritem.setting.substr(xritem.setting.lastIndexOf('/')+1)+'</td><td align=\'justify\' class=\'ui-widget-content no-background-image\' style=\'border:0;padding:5px;max-width:100px;width:100px;height:20px;max-height:20px;text-align:center!important;\'> - </td><td align=\'justify\' class=\'ui-widget-content no-background-image\'  style=\'border:0;padding:1px 5px 5px 5px;height:20px;max-height:20px;max-width:100%;width:100%;text-align:right!important;\'><span>Data Extraction Error: 0x'+obj.error.toString(16)+'</span></td></tr>';
											}
										}
									});
									htbl += '</tbody></table></div>';
								}
								jQuery('#xr_kinfo').html(htbl);
								jQuery('table.xr-list').tablesorter({
									theme: 'jui',
									widgets : ['uitheme'],
									emptyTo: 'bottom',
									//widgetOptions: {uitheme : 'jui'},	// this is now optional in v2.7, it is overridden by the theme option
									sortList: [[0,0],[1,0]] 			// sort on the first column and second column in ascending order
								});
								jQuery('table.xr-list thead th:eq(5)').data('sorter', false);
								jQuery('#xr_cpath').text('/'+data.instance.get_fullpath(data.node));
								jQuery('.preloader-diag').removeClass('ui-helper-hidden').addClass('ui-helper-hidden');
							}, 0);
							return true;
						});
						
						function xregfilter(e){
							e.preventDefault();
							e.stopPropagation();
							e.stopImmediatePropagation();
							var nval = jQuery.trim(e.target.value);
							var trset = jQuery('tr.xreg-setting');
							if(nval===xkfilter){return;}
							if(nval!=='*' && nval!==''){
								jQuery.each(trset,function(ixs,xtem){
									var xsr = jQuery(xtem);
									var stg = JSON.parse(xsr.attr('data-xs')).setting;
									stg = stg.substr(stg.lastIndexOf('/')+1);
									if(stg.indexOf(nval)<0){
										jQuery.each(xsr.children('td'),function(ixc,ctem){
											ctem=jQuery(ctem);
											if(ctem.hasClass('xreg-filtered')){
												ctem.remove('xreg-filtered');
											}
										});
										if(!xsr.hasClass('ui-helper-hidden')){
											xsr.addClass('ui-helper-hidden');
										}
									}
									else{
										if(xsr.hasClass('ui-helper-hidden')){
											xsr.removeClass('ui-helper-hidden');
										}
										jQuery.each(xsr.children('td'),function(ixc,ctem){
											ctem=jQuery(ctem);
											if(!ctem.hasClass('xreg-filtered')){
												ctem.addClass('xreg-filtered');
											}
										});
									}
								});
								jQuery(e.target).parent().parent().parent().find('li.context-menu-disabled').not('.context-menu-input').removeClass('context-menu-disabled cm-disabled').addClass('cm-disabled');
								xkfilter=nval;
							}
							else{
								trset.removeClass('ui-helper-hidden');
								trset.find('td').removeClass('xreg-filtered');
								jQuery(e.target).parent().parent().parent().find('li.cm-disabled').not('.context-menu-input').removeClass('context-menu-disabled cm-disabled').addClass('context-menu-disabled');
								xkfilter='*';
							}
						}
						jQuery.contextMenu({
							selector: 'i.ellipsis', 
							trigger: 'left',
							delay: 200,
							build: function(jQtrigger, e) {
								e.preventDefault();
								e.stopPropagation();
								e.stopImmediatePropagation();
								
								var xtree = jst_xr.jstree(true);
								console.log('in cm');
								console.log(xdfilter);
								return {
									callback: function(key, options) {
										switch(key){
											case 'sortX':
												xr_asc = !xr_asc;
												xtree.refresh();
												var icn = jQuery(this).find('i');
												if(icn.hasClass('fa-sort-alpha-desc')){
													icn.removeClass('fa-sort-alpha-desc').addClass('fa-sort-alpha-asc');
												}
												else if(icn.hasClass('fa-sort-alpha-asc')){
													icn.removeClass('fa-sort-alpha-asc').addClass('fa-sort-alpha-desc');
												}
												if(xdfilter!=='*' && !xdfilter.length){
													xtree.deselect_all();
													xtree.search(xdfilter.pattern,true,true,xdfilter.parent,false);
												}
												break;
											case 'clearfilterX':
												//jQuery('input[name=\'context-menu-input-ipt_filter\']').val('*');
												xtree.clear_search();
												xdfilter='*';
												break;
											default:
												break;
										}
									},
									items: {
										'sortX': {
											name: xr_asc === false ? 'Sort xRegistry Directories A-Z':'Sort xRegistry Directories Z-A', 
											icon: function(jQel, key, item){ return xr_asc === false ? 'context-menu-icon context-menu-icon--fa fa fa-sort-alpha-asc':'context-menu-icon context-menu-icon--fa fa fa-sort-alpha-desc'; },
											disabled: function(){ return false; }
										},
										'filterkeymenu': {
											name: 'Filter xRegistry Directory', 
											icon: function(jQel, key, item){ return 'context-menu-icon context-menu-icon--fa fa fa-search'; },
											disabled: function(){ return xtree.get_node(jQtrigger[0].parentElement.id).children.length<=0 ? true:false; },
											items: {
												'ipt_filter': {
													name: 'Filter Pattern',
													type:'text',
													value: xdfilter.length ? xdfilter: xdfilter.dir===xtree.get_fullpath(xtree.get_node(jQtrigger[0].parentElement.id)) ? xdfilter.pattern : '*',
													events: {
														focusin: function(e) {
															e.preventDefault();
															e.stopPropagation();
															e.stopImmediatePropagation();
														},
														focusout: function(e) {
															if(e.target.value===''){
																e.target.value='*';
																xtree.clear_search();
																xdfilter='*';
															}
														},
														click:function(e){
															e.preventDefault();
															e.stopPropagation();
															e.stopImmediatePropagation();
															e.target.focus();
														},
														keyup: function(e) {
															if(e.target.value.length>0){
																if(e.target.value!=='*'){
																	xdfilter={"dir":xtree.get_fullpath(xtree.get_node(jQtrigger[0].parentElement.id)), "parent":jQtrigger[0].parentElement.id,"pattern":e.target.value, "ellipsis":jQtrigger[0].id};
																	jQuery(e.target).parent().parent().parent().find('li.context-menu-disabled').not('.context-menu-input').removeClass('context-menu-disabled cm-disabled').addClass('cm-disabled');
																	console.log('keyup cm search');
																	console.log('Searching jQtrigger[0].parentElement.id '+jQtrigger[0].parentElement.id);
																	xtree.search(e.target.value,true,true,jQtrigger[0].parentElement.id,false);
																}
																else{
																	jQuery(e.target).parent().parent().parent().find('li.cm-disabled').not('.context-menu-input').removeClass('context-menu-disabled cm-disabled').addClass('context-menu-disabled');
																	console.log('keyup cm clear_search');
																	xtree.clear_search();
																	xdfilter='*';
																}
															}
															console.log('keyup cm out');
															console.log(xdfilter);
															jQuery(document).tooltip();
														}
													},
													disabled: function(){ return false; }
												},
												'clearfilterX': {
													name: 'Clear Filter', 
													icon: function(jQel, key, item){ return 'context-menu-icon context-menu-icon--fa fa fa-remove'; },
													disabled: function(){ return xdfilter==='*'? true:false; }
												}
											}
										}
									}
								};
							}
						});
						jQuery.contextMenu({
							selector: 'div.xr-list-container',//#xr_kinfo
							trigger: 'left',
							delay: 50,
							autoHide: true,
							build: function(jQtrigger, e) {
								e.preventDefault();
								e.stopPropagation();
								e.stopImmediatePropagation();
								return {
									callback: function(key, options) {
										switch(key){
											case 'clearfilterXK':
												var trset = jQuery('tr.xreg-setting');
												trset.removeClass('ui-helper-hidden');
												trset.find('td').removeClass('xreg-filtered');
												xkfilter='*';
												break;
											default:
												break;
										}
									},
									items: {
										'filterkeymenu': {
											name: 'Filter xRegistry Keys', 
											icon: function(jQel, key, item){ return 'context-menu-icon context-menu-icon--fa fa fa-search'; },
											items: {
												'ipt_filterk': {
													name: 'Filter Key Pattern',
													type:'text',
													value: xkfilter,
													events: {
														focusin: function(e) {
															e.preventDefault();
															e.stopPropagation();
															e.stopImmediatePropagation();
														},
														focusout: function(e) {
															if(e.target.value===''){
																e.target.value='*';
																xkfilter='*';
															}
														},
														keyup: function(e) {
															xregfilter(e);
															jQuery(document).tooltip();
														}
													},
													disabled: function(){ return false; }
												},
												'clearfilterXK': {
													name: 'Clear Key Filter', 
													icon: function(jQel, key, item){ return 'context-menu-icon context-menu-icon--fa fa fa-remove'; },
													disabled: function(){ return xkfilter.length>0 && xkfilter!=='*' ? false:true; }
												}
											}
										}
									}
								};
							}
						});
						jQuery.contextMenu({
							selector: 'tr.xreg-setting',
							trigger: 'left',
							delay: 50,
							autoHide: true,
							build: function(jQtrigger, e) {
								e.preventDefault();
								e.stopPropagation();
								e.stopImmediatePropagation();
								var xs = JSON.parse(jQtrigger.attr('data-xs'));
								var sltype=['boolean','integer','string'];
								if(!xs){
									alert('error');
								}
								else{
									alert('type: '+sltype[xs.type]);
									alert('value: '+xs.value);
								}
								var inpt = jQuery('#'+xs.id);
								var trw = jQuery('#'+xs.row);
								var xroot = '/setting/';
								var xKey = {'setting':xs.setting,'type':sltype[xs.type],'value':xs.value};
								var xtree = jst_xr.jstree(true);
								return {
									callback: function(key, options) {
										switch(key){
											case 'delX':
												function confirmDelete(){
													setTimeout(function(){
														//var xdret = helper.xregistry.xdelete(xs.setting);
														var xdret = 0;
														if(xdret===0){
															inpt.off();
															trw.remove();
															//we need to remove the reference in the tree data too
															
															var node = xtree.get_node(jQuery('#currentxnode').val());
															var new_attr = {'data-xrf':[],'title':node.a_attr['title']};
															jQuery.each(node.a_attr['data-xrf'],function(xri,xritem){
																if(xritem.setting!==xs.setting){
																	new_attr['data-xrf'].push(xritem);
																}
															});
															node.a_attr=new_attr;
															toast('NOT YET IMPLEMENTED - Deleted xRegistry key '+xs.setting,'notice',5);
														}
														else{
															toast('NOT YET IMPLEMENTED - Error 0x'+xdret.toString(16)+' deleting xRegistry key '+xs.setting,'error',5);
														}
														
													},250);
												}
												confirmDialog('Deleting this registry item is allowed because you disabled Strict Mode. Proceeding with the deletion could be risky, you have been warned. Are you sure you want to continue?','Delete xRegistry Item Confirmation',confirmDelete,null,null,null,{'txt':'dg-text2','conf':'dg-confirm2'});
												break;
											case 'editX':
												function confirmEdit(){
													setTimeout(function(){
														//var xeret = helper.xregistry.xset(xs.setting);
														var xeret = 0;
														if(xeret===0){
															xtree.deselect_all();
															jQuery('#xr_kinfo').html('Refreshing data');
															xtree.select_node(xtree.get_node(jQuery('#currentxnode').val()));
															toast('NOT YET IMPLEMENTED - Edited xRegistry key '+xs.setting,'notice',5);
															// edit row cell or reload tree item by deselect then selecting node
														}
														else{
															toast('NOT YET IMPLEMENTED - Error 0x'+xeret.toString(16)+' deleting xRegistry key '+xs.setting,'error',5);
														}
														
													},250);
												}
												confirmDialog('Editing this registry item is allowed because you disabled Strict Mode. Proceeding with the edition could be risky, you have been warned. Are you sure you want to continue?','Edit xRegistry Item Confirmation',confirmEdit,null,null,null,{'txt':'dg-text2','conf':'dg-confirm2'});
												break;
											case 'clearfilterXK':
												//alert('clearfilterXK');
												var trset = jQuery('tr.xreg-setting');
												trset.removeClass('ui-helper-hidden');
												trset.find('td').removeClass('xreg-filtered');
												xkfilter='*';
												break;
											default:
												break;
										}
									},
									items: {
										'delX': {
											name: 'Delete xRegistry Item', 
											icon: function(jQel, key, item){ return 'context-menu-icon context-menu-icon--fa fa fa-minus'; },
											disabled: function(){ return helper.fm_usermode === 0 ? true:false; }
										},
										'editkeymenu': {
											name: 'Edit xRegistry Key',
											icon: function(jQel, key, item){ return 'context-menu-icon context-menu-icon--fa fa fa-edit'; },
											disabled: function(){ return helper.fm_usermode === 0 ? true:false; },
											items: {
												'ipt_setting': {
													name: 'Setting',
													type:'text',
													value: xs.setting,
													disabled: function(){ setTimeout(function(){var o = jQuery('input[name=\'context-menu-input-ipt_setting\']');o.attr('disabled',true);o.attr('title',o.val());jQuery(document).tooltip();},250);return false; }
												},
												'ipt_value': {
													name: 'Value',
													type:'text',
													value: xs.value,
													events: {
														focusin: function(e) {
															e.preventDefault();
															e.stopPropagation();
															e.stopImmediatePropagation();
															//if(xKey.type==='integer' && e.target.value==='0'){
															//	e.target.value = '';
															//}
														},
														focusout: function(e) {
														//alert('focusout');
															var isval_valid=false;
															if(e.target.value===''){
																if(xKey.type==='integer'){
																	if(xKey.value===''){
																		xKey.value=0;
																	}
																	e.target.value = '0x'+xKey.value.toString(16).toUpperCase();
																	isval_valid=true;
																}
																else if(xKey.type==='boolean'){
																	xKey.value=0;
																	e.target.value = xKey.value.toString();
																	isval_valid=true;
																}
																else if(xKey.type==='string'){
																	e.target.value = xKey.value;
																	isval_valid=true;
																}
															}
															else{
																if(xKey.type==='integer'){ // not 64bit compliant (xset/xadd are not 64bit compliant, it looks like the exports only use 32 bit integer params according to RE)
																	//alert('test focusout');
																	var nval = parseInt(e.target.value,16);
																	if(nval!==xKey.value){
																		if((isNaN(nval) && e.target.value.toUpperCase()!==nval.toString(16).toUpperCase()) || nval>0xFFFFFFFF){
																			//alert('test no focusout');
																		}
																		else{
																			xKey.value= nval;
																			//alert('test in focusout');
																			e.target.value = '0x'+xKey.value.toString(16).toUpperCase();
																			isval_valid=true;
																		}
																	}
																}
																else if(xKey.type==='string'){
																	xKey.value = e.target.value;
																	isval_valid=true;
																}
																else if(xKey.type==='boolean'){
																	if(e.target.value==='0' || e.target.value==='1'){
																		xKey.value = parseInt(e.target.value);
																		isval_valid=true;
																	}
																}
															}
															var cval = xKey.value;
															if(xKey.type==='boolean'){
																cval =  xKey.value.toString(16);
															}
															else if(xKey.type==='integer'){
																cval = '0x'+ xKey.value.toString(16);
															}
															//need to test this cval comp with xs.value...
															//if(isval_valid && !(xKey.type===sltype[xs.type] && cval===xs.value)){
															if(isval_valid  && cval!==xs.value){
																jQuery(e.target).parent().parent().parent().find('li.context-menu-disabled').not('.context-menu-input').removeClass('context-menu-disabled cm-disabled').addClass('cm-disabled');
															}
															else{
																jQuery(e.target).parent().parent().parent().find('li.cm-disabled').not('.context-menu-input').removeClass('context-menu-disabled cm-disabled').addClass('context-menu-disabled');
															}
														},
														keyup: function(e) {
															var isval_valid=false;
															if(e.target.value!=='' ){
																if(xKey.type==='integer'){ // not 64bit compliant (xset/xadd are not 64bit compliant, it looks like the exports only use 32 bit integer params according to RE)
																	var cva = e.target.value.indexOf('0x')===0 ? e.target.value : '0x'+e.target.value;
																	if(cva==='0x'){
																		cva = '0x0';
																	}
																	var nval = parseInt(cva,16);
																	if(nval!==xKey.value || e.target.value==='0x' || parseInt(e.target.value,16)===0){
																		if((isNaN(nval) && cva.toUpperCase().substr(2)!==nval.toString(16).toUpperCase()) || nval>0xFFFFFFFF){
																			xKey.value= 0;
																		}
																		else{
																			xKey.value= nval;
																			if(e.target.value!=='0x'){
																				e.target.value = '0x'+xKey.value.toString(16).toUpperCase();
																				isval_valid=true;
																			}
																		}
																	}
																}
																else if(xKey.type==='string'){
																	xKey.value = e.target.value;
																	isval_valid=true;
																}
																else if(xKey.type==='boolean'){
																	if(parseInt(e.target.value)===0 || parseInt(e.target.value)===1){
																		xKey.value = parseInt(e.target.value);
																		isval_valid=true;
																	}
																}
															}
															else{
																if(xKey.type==='string'){
																	isval_valid=true;
																}
															}
															if(isval_valid){
																if(xKey.type==='string'){
																	if(xKey.value!==xs.value){
																		jQuery(e.target).parent().parent().parent().find('li.context-menu-disabled').not('.context-menu-input').removeClass('context-menu-disabled cm-disabled').addClass('cm-disabled');
																	}
																	else{
																		jQuery(e.target).parent().parent().parent().find('li.cm-disabled').not('.context-menu-input').removeClass('context-menu-disabled cm-disabled').addClass('context-menu-disabled');
																	}
																}
																else if(xKey.type==='integer'){
																	if('0x'+xKey.value.toString(16)!==xs.value){
																		jQuery(e.target).parent().parent().parent().find('li.context-menu-disabled').not('.context-menu-input').removeClass('context-menu-disabled cm-disabled').addClass('cm-disabled');
																	}
																	else{
																		jQuery(e.target).parent().parent().parent().find('li.cm-disabled').not('.context-menu-input').removeClass('context-menu-disabled cm-disabled').addClass('context-menu-disabled');
																	}
																}
																else if(xKey.type==='boolean'){
																	if(xKey.value!==parseInt(xs.value,16)){
																		jQuery(e.target).parent().parent().parent().find('li.context-menu-disabled').not('.context-menu-input').removeClass('context-menu-disabled cm-disabled').addClass('cm-disabled');
																	}
																	else{
																		jQuery(e.target).parent().parent().parent().find('li.cm-disabled').not('.context-menu-input').removeClass('context-menu-disabled cm-disabled').addClass('context-menu-disabled');
																	}
																}
															}
															else{
																jQuery(e.target).parent().parent().parent().find('li.cm-disabled').not('.context-menu-input').removeClass('context-menu-disabled cm-disabled').addClass('context-menu-disabled');
															}
															jQuery(document).tooltip();
														}
													},
													disabled: function(){ return helper.fm_usermode === 0 ? true:false; }
												},
												'sel_type': {
													name: 'Type',
													type: 'select', 
													options: sltype, 
													selected: xs.type,
													events: {
														focusin: function(e) {
															e.preventDefault();
															e.stopPropagation();
															e.stopImmediatePropagation();
															//if(xKey.type==='integer' && e.target.value==='0'){
															//	e.target.value = '';
															//}
														},
														change: function(e) {
															xKey.type = sltype[e.target.selectedIndex];
															console.log(xKey.type);
															
															if(xKey.type==='boolean'){
																var cvl = parseInt(xKey.value,16);
																xKey.value = isNaN(cvl) ? 0 : cvl > 1 ? 1 : cvl;
																jQuery('input[name=\'context-menu-input-ipt_value\']').val(xKey.value.toString());
															}
															else if(xKey.type==='integer'){
																var cvl = parseInt(xKey.value,16);
																xKey.value = isNaN(cvl) ? 0 : cvl > 0xFFFFFFFF ? 0xFFFFFFFF : cvl;
																jQuery('input[name=\'context-menu-input-ipt_value\']').val('0x'+xKey.value.toString(16).toUpperCase());
															}
															else{
																if(typeof xKey.value!=='string'){
																	xKey.value = xKey.value.toString(16);
																	jQuery('input[name=\'context-menu-input-ipt_value\']').val(xKey.value);
																}
															}
															
															if((xKey.type==='boolean' && xKey.value>1) || (xKey.type==='boolean' && xKey.value<0) || (xKey.type==='boolean' && xKey.type===sltype[xs.type] && xKey.value===parseInt(xs.value,16)) || (xKey.type==='integer' && isNaN(xKey.value))){
																jQuery(e.target).parent().parent().parent().find('li.cm-disabled').not('.context-menu-input').removeClass('context-menu-disabled cm-disabled').addClass('context-menu-disabled');
															}
															else if((xKey.type==='string' && xKey.type===sltype[xs.type] && xKey.value===xs.value) || (xKey.type!=='string' && xKey.type===sltype[xs.type] && xKey.value.toString(16)===xs.value)){
																jQuery(e.target).parent().parent().parent().find('li.cm-disabled').not('.context-menu-input').removeClass('context-menu-disabled cm-disabled').addClass('context-menu-disabled');
															}
															else{
																jQuery(e.target).parent().parent().parent().find('li.context-menu-disabled').not('.context-menu-input').removeClass('context-menu-disabled cm-disabled').addClass('cm-disabled');
															}
														}
													},
													disabled: function(){ return helper.fm_usermode === 0 ? true:false; }
												},
												'editX': {
													name: 'Edit xRegistry Key', 
													icon: function(jQel, key, item){ return 'context-menu-icon context-menu-icon--fa fa fa-edit'; },
													disabled: function(){ return true; }
												}
											}
										},
										'filterkeymenu': {
											name: 'Filter xRegistry Keys', 
											icon: function(jQel, key, item){ return 'context-menu-icon context-menu-icon--fa fa fa-search'; },
											items: {
												'ipt_filterk': {
													name: 'Filter Key Pattern',
													type:'text',
													value: xkfilter,
													events: {
														focusin: function(e) {
															e.preventDefault();
															e.stopPropagation();
															e.stopImmediatePropagation();
														},
														focusout: function(e) {
															if(e.target.value===''){
																e.target.value='*';
																xkfilter='*';
															}
														},
														keyup: function(e) {
															xregfilter(e);
															if(helper.fm_usermode !== 0){
																var list = jQuery(e.target).parent().parent().parent().parent().parent().children();
																if(trw.hasClass('ui-helper-hidden')){
																	jQuery(list[0]).removeClass('context-menu-disabled').addClass('context-menu-disabled');
																	jQuery(list[1]).removeClass('context-menu-disabled').addClass('context-menu-disabled');
																}
																else {
																	jQuery(list[0]).removeClass('context-menu-disabled');
																	jQuery(list[1]).removeClass('context-menu-disabled');
																}
															}
															jQuery(document).tooltip();
														}
													},
													disabled: function(){ return false; }
												},
												'clearfilterXK': {
													name: 'Clear Key Filter', 
													icon: function(jQel, key, item){ return 'context-menu-icon context-menu-icon--fa fa fa-remove'; },
													disabled: function(){ return xkfilter.length>0 && xkfilter!=='*' ? false:true; }
												}
											}
										}
									}
								};
							}
						});
					}
				}
				var so =null;
				jQuery('.preloader').removeClass('ui-helper-hidden');
				jQuery('#admode').switchButton({
					labels_placement: 'left',
					//checked: true,
					clear: false,
					on_label: 'SM Strict Mode ON ',
					off_label: 'SM Strict Mode OFF',
					on_callback: function (){
						jQuery(document).tooltip('disable');
						helper.fm_usermode = 0;
						jQuery(document).tooltip('enable');
					},
					off_callback: function(){
						jQuery(document).tooltip('disable');
						function confirmMode(){
							helper.fm_usermode = 1;
							jQuery(document).tooltip('enable');
						}
						confirmDialog('YOU SHOULD NEVER TURN SM Strict Mode OFF!!!<br><br>Only advanced users & developers should ever consider using SM with strict mode off.You have been warned.','Are you sure you want to continue?',confirmMode,null,function(ck){jQuery('#admode').switchButton('option','checked', ck);jQuery(document).tooltip('enable');},true);
					}
				});

				var fTree = function(close_toast){
					var jQtree = jQuery('.fm-tree');
					so = so ? so : new storageObject();
					var mv = getMinVer();
					if(mv.error!==0){
						Logger.error('The minimum applicable firmware version could not be extracted - Error 0x'+mv.error);
						toast('The minimum applicable firmware version could not be extracted. Please reboot & try again.','error',5);
						return;
					}
					helper.minver = mv.version;
					var cfwminver = parseFloat(helper.minver)<3.60;
					var metldr = getMtldrVersion(so);
					function metldr_err(){
						Logger.error('The minimum applicable firmware version does not match the metldr version');
						Logger.warn('If the IDPS of your console is spoofed, the minimum applicable firmware version calculated by the system is no longer reliable');
						toast('A discrepancy possibly caused by IDPS spoofing was detected in the minimum applicable firmware version returned by the system.','warning',5);
						helper.minver += ' !';
					}
					if(metldr === 'metldr.2' && cfwminver){
						metldr_err();
					}
					else if(metldr === 'metldr' && !cfwminver){
						metldr_err();
					}
					var cfw_compat = metldr!=='metldr.2';
					var compat = jQuery('#cfwcompat');
					compat.parent().addClass(cfw_compat ? 'header-label on':'header off');
					compat.addClass(cfw_compat ? 'fa fa-check fa-lg fa-fw':'fa fa-times fa-lg fa-fw');	
					compat.css({'color':cfw_compat ? '#99c700':'#e95136','font-size':'20px','width':'30px','position':'relative','padding-top':'1px','text-shadow':'-1px -1px 0 #000,0 -1px 0 #000,1px -1px 0 #000,1px 0 0 #000,1px 1px 0 #000,0 1px 0 #000,-1px  1px 0 #000,-1px 0 0 #000'});
					var _nor = so.is_nor();
					//var idps = getIDPS(so,_nor ? helper.idps_sector_nor : helper.idps_sector_nand);
					var psid = '';
					var pcode = '';
					var pscode = '';
					var extIDs = getIDs();
					for(var iid=0;iid<extIDs.length;iid++){
						//alert(extIDs[iid].error===0 ? extIDs[iid].id+': '+extIDs[iid].value : extIDs[iid].id+' error: '+extIDs[iid].error);
						if(extIDs[iid].id==='PSID'){
							if(extIDs[iid].error===0){
								psid = extIDs[iid].value;
							}
							else{
								Logger.error(extIDs[iid].error);
							}
						}
						else if(extIDs[iid].id==='IDPS'){
							if(extIDs[iid].error===0){
								idps = extIDs[iid].value;
							}
							else{
								Logger.error(extIDs[iid].error);
							}
						}
						else if(extIDs[iid].id==='Product Code'){
							if(extIDs[iid].error===0){
								pcode = extIDs[iid].value;
							}
							else{
								Logger.error(extIDs[iid].error);
							}
						}
						else if(extIDs[iid].id==='Product SubCode'){
							if(extIDs[iid].error===0){
								pscode = extIDs[iid].value;
							}
							else{
								Logger.error(extIDs[iid].error);
							}
						}
					}
					var XXX = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
					var idps_hidden = false;
					var psid_hidden = false;
					var jstree;
					jQtree.jstree({
						'core' : {
							'multiple':false,
							'restore_focus':false,
							'dblclick_toggle':false,
							'data' : function (node, cb) {
								if(node.id === '#') {
									var nodes = [{ 'id' : 'flash', 'type' : 'flash', 'parent' : '#', 'text' : 'Flash Memory' },
									   { 'id' : 'type', 'type' : 'ros', 'parent' : 'flash', 'text' : 'Type: ?' },
									   { 'id' : 'sectors', 'type' : 'ros', 'parent' : 'flash', 'text' : 'Number of Sectors: ?' },
									   { 'id' : 'minver', 'type' : 'ros', 'parent' : 'flash', 'text' : 'Minimum Applicable FW Version: ?' },
									   { 'id' : 'ros0', 'type' : 'ros', 'parent' : 'flash', 'text' : 'ROS bank 0' },
									   { 'id' : 'ros1', 'type' : 'ros', 'parent' : 'flash', 'text' : 'ROS bank 1' },
									   { 'id' : 'info0', 'type' : 'info', 'parent' : 'ros0', 'text' : 'Calculating SHA256 checksum, please wait...' },
									   { 'id' : 'info1', 'type' : 'info', 'parent' : 'ros1', 'text' : 'Calculating SHA256 checksum, please wait...' },
									   { 'id' : 'flashbk', 'type' : 'flash', 'parent' : '#', 'text' : 'Flash Memory Patch' },
									   { 'id' : 'system', 'type' : 'sysinfo', 'parent' : '#', 'text' : 'System Info' },
									   { 'id' : 'firmware', 'type' : 'sinfo', 'parent' : 'system', 'text' : 'Firmware Version: ?' },
									   { 'id' : 'platform', 'type' : 'sinfo', 'parent' : 'system', 'text' : 'Platform ID: ?' },
									   { 'id' : 'board', 'type' : 'sinfo', 'parent' : 'system', 'text' : 'Board ID: ?' },
									   { 'id' : 'idps', 'type' : 'sinfo', 'parent' : 'system', 'text' : 'IDPS: ?' },
									   { 'id' : 'psid', 'type' : 'sinfo', 'parent' : 'system', 'text' : 'PSID: ?' },
									   { 'id' : 'pcode', 'type' : 'sinfo', 'parent' : 'system', 'text' : 'Product Code: ?' },
									   { 'id' : 'pscode', 'type' : 'sinfo', 'parent' : 'system', 'text' : 'Product SubCode: ?' },
									   { 'id' : 'hwconfig', 'type' : 'sinfo', 'parent' : 'system', 'text' : 'Hardware Config: ?' },
									   { 'id' : 'scver', 'type' : 'sinfo', 'parent' : 'system', 'text' : 'Syscon Firmware Version: ?' },
									   { 'id' : 'onstats', 'type' : 'sinfo', 'parent' : 'system', 'text' : 'Statistics: ?' },
									   { 'id' : 'errors', 'type' : 'scerrors', 'parent' : 'system', 'text' : 'Errors (?)' }
									];
									cb(nodes);
								 }
							},
							'check_callback' : function (operation, node, node_parent, node_position, more) {
								var ret = false;
								switch(operation){
									case 'create_node':
										ret= true;
										break;
									case 'rename_node':
										ret= true;
										break;
									case 'delete_node':
										ret= true;
										break;
									case 'move_node':
										ret= false;
										break;
									case 'copy_node':
										ret= false;
										break;
									case 'edit':
										ret= false;
										break;
									default:
										ret= false;
								}
								return ret;
							}
						},
						'themes':{
							'dots': true,
							'icons': true
						},
						'sort' : function(a, b) {
							return (this.get_node(a).text > this.get_node(b).text) ? 1 : -1;
						},
						'types' : {
							'#' : {
							  'max_children' : 3,
							  'max_depth' : 3,
							  'valid_children' : ['flash']
							},
							'flash' : {
							 'max_children' : 2,
							  'max_depth' : 2,
							  'icon' : 'jstree-folder',
							  'valid_children' : ['ros']
							},
							'ros' : {
							  'max_children' : 1,
							  'max_depth' : 1,
							  'icon' : 'jstree-folder',
							  'valid_children' : ['info']
							},
							'info' : {
							  'max_children' : 0,
							  'max_depth' : 0,
							  'icon' : 'jstree-file',
							  'valid_children' : []
							},
							'sysinfo' : {
							  'max_children' : 10,
							  'max_depth' : 2,
							  'icon' : 'jstree-folder',
							  'valid_children' : ['sinfo','scerrors']
							},
							'sinfo' : {
							  'max_children' : 0,
							  'max_depth' : 0,
							  'icon' : 'fa fa-microchip',
							  'valid_children' : []
							},
							'scerrors' : {
							  'max_children' : 32,
							  'max_depth' : 1,
							  'icon' : 'jstree-folder',
							  'valid_children' : ['scerror']
							},
							'scerror' : {
							  'max_children' : 0,
							  'max_depth' : 0,
							  'icon' : 'fa fa-flag',
							  'valid_children' : []
							}
						},
						'contextmenu' :{
							'show_at_node':true,
							'items': function(node) {
								//alert('node '+node.id);
								var is_regmode = helper.fm_usermode === 0;
								var is_patch_rec = helper.nofsm_hash === helper.sha256_loadedros;
								var is_cex = helper.kmode === 'CEX';
								var _node = jstree.get_node('flashbk');
								if(jstree.is_disabled('flashbk') && node.id === 'flashbk'){return {};}
								var is_patch_avail = _node ? _node.children.length > 0 ? true : false : false;
								var ret = node.id === 'flash' ? {
										'Save': {
											'separator_before': false,
											'separator_after': false,
											'label': 'Save Flash Memory Backup',
											'icon' : 'fa fa-floppy-o fa-fw',
											'action': function (obj) {
												setTimeout(function(){
													sdiag.open({
														'sector_count': _nor ? 0x8000 : 0x77800,
														'nsec_iter': _nor ? 0x2000 : 0x8000,//nand 0x8000 (16Mb) - nor: 0x2000 (4Mb)
														'dump_start': 0,
														'save_offset':0,
														'file_path': '',
														'default_name': 'dump.hex',
														'tls': null,
														'pre_callback':removePatch,
														'buffer': null
													},mt_dump);
												},0);
											}
										}
									} : (node.id === 'flashbk') ? {
										'Load': {
											'separator_before': false,
											'separator_after': false,
											'label': 'Load Patch from file',
											'icon' : 'fa fa-folder-open-o fa-fw',
											'action': function (obj) {
												removePatch();
												setTimeout(function(){
													ldiag.open({'buffer': null,'pre_callback': null,'patch': true});
												},0);
											}
										},
										'LoadWeb':{
											'separator_before': false,
											'separator_after': false,
											'label':  'Load Patch via HTTPS',
											'_disabled': is_cex ? helper.nofsm_url.length>0 ? false: true : true, //add more checks????
											'icon' : 'fa fa-cloud-download fa-fw',
											'action': function (obj) {
												setTimeout(function(){
													jQuery('.preloader').removeClass('ui-helper-hidden');
													removePatch();
													
													setTimeout(function() {
														helper.rosBuffer = helper.sm.getBuffer();
														dl_object  = {'buffer': helper.rosBuffer,'file': helper.nofsm_url,'start':new Date(),'sha256':''};
														pbfm1.ulog(dl_object.start,true);
														if(!dl_object.buffer){Logger.error('loadPatch: Buffer memory allocation failed!');toast('Buffer memory allocation failed','error',5);return;}
														pbfm1.open(false,dl_cancel);
														pbfm1.updateStatusText('Initializing download operations');
														pbfm1.updateProgressDialog({'glabel':'Establishing server connection','title':'Download Operations Progress'});
														
														setTimeout(function() {
															helper.swf.downloadFile(dl_object.file,dl_object.buffer.offset+0x30,true);//
														},500);
													},500);
												},0);
											}
										},
										'Download':{
											'separator_before': false,
											'separator_after': false,
											'label':  'Download Patch file',
											'_disabled': is_cex ? helper.nofsm_url.length>0 ? false: true : true, //add more checks????
											'icon' : 'fa fa-download fa-fw',
											'action': function (obj) {
												setTimeout(function(){
													jQuery('.preloader').removeClass('ui-helper-hidden');
													helper.rosBuffer = helper.sm.getBuffer();
													if(helper.rosBuffer){
														function save_patch(pobj){
															setTimeout(function() {
																dl_object  = pobj;
																pbfm1.ulog(dl_object.start,true);
																if(!dl_object.buffer){
																	Logger.error('loadPatch: Buffer memory allocation failed!');
																	toast('Buffer memory allocation failed','error',5);
																	return;
																}
																pbfm1.open(false,dl_cancel);
																//pbfm1.updateStatusText('Initializing download operations');
																pbfm1.updateProgressDialog({'glabel':'Establishing server connection','title':'Download Operations Progress','istatus':'Initializing download operations'});
																setTimeout(function() {
																	helper.swf.downloadFile(dl_object.default_name,dl_object.buffer.offset,false);//
																},500);
															},500);
														}
														sdiag.open({
															'file_path': '',
															'default_task': 'noFSM Patch File',
															'default_name': helper.nofsm_url,
															'buffer': helper.rosBuffer,
															'start': new Date(),
															'pre_callback': removePatch,
															'no_pbar': true
														}, save_patch);
													}
													else{
														// display warning...
													}
												},0);
											}
										},
										'Patch': {
											'separator_before': true,
											'separator_after': false,
											'label': 'Apply loaded Patch',
											'icon' : 'fa fa-cogs fa-fw',
											'_disabled': is_regmode ? is_patch_rec && is_cex && is_patch_avail ? false : true : is_patch_avail ? false : true , //add more checks????
											'action': function (obj) {
												function confirmPatch(){
													jQuery('.preloader').removeClass('ui-helper-hidden');
													var def = jQuery.Deferred();
													def.promise().done(mt_patch);
													setTimeout(function(){
														var patch_object = {
															'sector_count': 0x7000,
															'patch_start': _nor ? 0x600 : 0x400,
															//'data_buffer': window.ldiag.getBuffer(),
															//'pre_callback':removePatch,
															'data_buffer': helper.rosBuffer,
															'offset_data':{'ros0':_nor ? 0x20 : 0, 'ros1': _nor ? 0x20 : 0x10}
														};
														pbfm1.setTitle('Patching Operations Progress');
														pbfm1.open(true);
														def.resolve(patch_object);
													},0);
												}
												if(!is_patch_rec){ //check against offcial no-fsm patch sha256 ??
													confirmDialog('Patching the ps3 Flash Memory with this patch file is allowed because you disabled Strict Mode. Proceeding to patching using this data could be seriously risky, you have been warned. There is no way to pause or cancel the patching process beyond this confirmation dialog. Are you sure you want to continue?','Patch Confirmation',confirmPatch);
												}
												else{
													confirmDialog('Patching the ps3 Flash Memory can brick your console, it should never be done casually. There is no way to pause or cancel the patching process beyond this confirmation dialog. Are you sure you want to continue?','Patch Confirmation',confirmPatch);
												}
											}
										}
									}: node.id === 'ros0' || node.id === 'ros1' ? {
										'Save_ROS': {
											'separator_before': false,
											'separator_after': false,
											'label': node.id === 'ros0' ? 'Save ROS0 data as noFSM Patch File':'Save ROS1 data as noFSM Patch File',
											'icon' : 'fa fa-floppy-o fa-fw',
											'_disabled': is_regmode ? true:false,
											'action': function (obj) {
												setTimeout(function(){
													sdiag.open(node.id === 'ros0' ? {
															'sector_count': 0x3800,
															'nsec_iter': 0x3800,//0x3800(7Mb)
															'dump_start': _nor ? 0x600:0x400,
															'save_offset': _nor ? 0x10:0x30,
															'file_path': '',
															'pre_callback': removePatch,
															'default_name': 'ros0.hex',
															'buffer': null
														}: {
															'sector_count': 0x3800,
															'nsec_iter': 0x3800,//0x3800(7Mb)
															'dump_start': _nor ? 0x3E00:0x3C00,
															'save_offset': _nor ? 0x10:0x20,
															'file_path': '',
															'pre_callback': removePatch,
															'default_name': 'ros1.hex',
															'buffer': null
														}, mt_dump);
												},0);
											}
										}
									}: (node.id === 'idps') ? {
										'Toggle': {
											'separator_before': false,
											'separator_after': false,
											'label': idps_hidden ? 'Show IDPS': 'Hide IDPS',
											'icon' : idps_hidden ? 'fa fa-unlock-alt fa-fw': 'fa fa-lock fa-fw',
											'action': function (obj) {
												idps_hidden ? jstree.rename_node('idps', 'IDPS: '+idps.toUpperCase()) : jstree.rename_node('idps', 'IDPS: '+XXX);
												idps_hidden = !idps_hidden;
											}
										},
										'Save':{
											'separator_before': false,
											'separator_after': false,
											'label':  'Save IDPS as file',
											'icon' : 'fa fa-floppy-o fa-fw',
											'action': function (obj) {
												setTimeout(function(){
													sdiag.open({
															'file_path': '',
															'default_task': 'IDPS',
															'default_name': 'idps.hex',
															'buffer': {offset:helper.heap.store(idps,false), size:idps.length/2},
															'no_pbar': false
														}, save_info);
												},0);
											}
										}
									}: (node.id === 'psid') ? {
										'Toggle': {
											'separator_before': false,
											'separator_after': false,
											'label': psid_hidden ? 'Show PSID': 'Hide PSID',
											'icon' : psid_hidden ? 'fa fa-unlock-alt fa-fw': 'fa fa-lock fa-fw',
											'action': function (obj) {
												psid_hidden ? jstree.rename_node('psid', 'PSID: '+psid) : jstree.rename_node('psid', 'PSID: '+XXX);
												psid_hidden = !psid_hidden;
											}
										},
										'Save':{
											'separator_before': false,
											'separator_after': false,
											'label':  'Save PSID as file',
											'icon' : 'fa fa-floppy-o fa-fw',
											'action': function (obj) {
												setTimeout(function(){
													sdiag.open({
															'file_path': '',
															'default_task': 'PSID',
															'default_name': 'psid.hex',
															'buffer': {offset:helper.heap.store(psid,false), size:psid.length/2},
															'no_pbar': false
														}, save_info);
												},0);
											}
										}
									}: (node.id === 'system') ? {
										'Save_Info': {
											'separator_before': false,
											'separator_after': false,
											'label': 'Save info to file',
											'icon' : 'fa fa-floppy-o fa-fw',
											'action': function (obj) {
												setTimeout(function(){
													sdiag.open({
															'file_path': '',
															'default_task': 'System Info',
															'default_name': 'sysinfo.log',
															'buffer': {offset:helper.heap.store(helper.sysinfo.txt,true),size:helper.sysinfo.txt.length},
															'no_pbar': false
														}, save_info);
												},0);
											}
										},
										'View_XReg': {
											'separator_before': false,
											'separator_after': false,
											'label': 'xRegistry.sys Editor',
											'icon' : 'fa fa-table fa-fw',
											'_disabled': true, //is_regmode ? true:false,
											'action': function (obj) {
												setTimeout(function(){
													htmlDialog('<div id=\'xreg_table\' style=\'max-height:500px;height:500px;max-width:750px;width:750px;\'><div class=\'pre-loader preloader-diag ui-helper-hidden\'><div class=\'container-busy-icon\'><div class=\'busy-icon\'></div></div></div><table class=\'\' style=\'width:100%;height:100%;border:0!important;\'><thead class=\'\'><tr class=\'\'><th class=\'ui-widget-header\' style=\'border:0!important;height:28px !important;max-height:28px !important;min-height:28px !important;\'><input id=\'currentxnode\' name=\'currentxnode\' type=\'hidden\' value=\'\'/><span>xRegistry Directories</span></th><th class=\'ui-widget-header\' style=\'border:0!important;height:28px !important;max-height:28px !important;min-height:28px !important;width:225px!important;min-width:250px!important;max-width:250px!important;\'></th></tr></thead><tfoot class=\'ui-widget-header\' style=\'border:0!important;height:28px !important;max-height:28px !important;min-height:28px !important;\'><tr><td><div id=\'xr_cpath\' class=\'\' style=\'padding-left:5px;border:0;width:100%;max-width:220px;overflow:visible;\'>/</div></td><td></td></tr></tfoot><tbody class=\'\' style=\'max-height:480px;\'><tr class=\'ui-widget-content no-background-image\'><td align=\'justify\' class=\'ui-widget-content no-background-image\' style=\'overflow:hidden;padding:25px 5px 5px 5px;max-height:50%;max-width:33%;width:33%;\'><div id=\'xrcontainer\' class=\'ui-corner-all ui-widget-content no-background-image\' style=\'overflow:hidden; border:0;\'><div id=\'contxr_ktree\' class=\'xrtree scb-xr1 ui-widget-content no-background-image\' style=\'border:0;\'><div id=\'xr_ktree\' class=\'ui-corner-all ui-widget-content no-background-image\' style=\'border:0;width:250px !important;max-width:250px !important;\'> Loading xRegistry contents, please wait... </div></div></div></td><td align=\'justify\' class=\'ui-widget-content no-background-image\'  style=\'overflow:hidden;padding:5px;max-height:100%;min-width:355px;max-width:355px;width:100%;vertical-align: text-top;\'><div id=\'xr_kinfo\' class=\'ui-corner-all scb-xr2 ui-widget-content no-background-image\' style=\'border:0;height:94%;\'> Select a xRegistry directory to display the keys it contains </div></td></tr></tbody></table></div>','xRegistry.sys Editor',xRegTableCleanup,null,function(){setTimeout(createXRegistryTable,50);});
													//htmlDialog(testXRegTable(),'xRegistry.sys TEST',null,null,function(){alert('done');});
												},0);
											}
										}
									}:{};
								return ret;
							}
						},
						'conditionalselect' : function (node, event) {
							if(node.type === 'flash' || node.id === 'ros0' || node.id === 'ros1'|| node.id === 'idps' || node.id === 'psid'|| node.id === 'system'){return true;}
							else {return false;}
						},
						'plugins' : [
							'search', 'types', 'changed', 'contextmenu', 'unique', 'conditionalselect' //,'sort'
						]
					});
					jQtree.on('select_node.jstree', function (e, data) {
						var evt =  window.event || e;
						var button = evt.which || evt.button;
						if( button != 1 && ( typeof button != undefined)) 
							return false; 
						else if(data.event){
							setTimeout(function() {
								data.instance.show_contextmenu(data.node, evt.offsetX, evt.offsetY, data.event);
							}, 0);
							return true;
						}
					});
					//alert('fmm ros hashing');
					jstree = jQtree.jstree(true);
					jstree.rename_node('type', _nor ? 'Flash Memory Type: NOR 16Mb' : cfw_compat ? 'Flash Memory Type: NAND 256Mb':'Flash Memory Type: eMMC 256Mb');
					jstree.rename_node('sectors', _nor ? 'Number of Sectors: 0x8000' : 'Number of Sectors: 0x80000 (0x77800 in dump)');
					jstree.rename_node('idps', 'IDPS: '+idps);
					jstree.rename_node('psid', 'PSID: '+psid);
					jstree.rename_node('pcode', 'Product Code: '+pcode);
					jstree.rename_node('pscode', 'Product SubCode: '+pscode);
					jstree.rename_node('minver','Minimum Applicable Firmware Version: '+helper.minver);
					if(!helper.sysinfo.done){
						helper.sysinfo.txt = 'PS3 System Information:\n';
						helper.sysinfo.txt += _nor ? 'Flash Memory Type: NOR 16Mb\n' : cfw_compat ? 'Flash Memory Type: NAND 256Mb\n':'Flash Memory Type: eMMC 256Mb\n';
						helper.sysinfo.txt += _nor ? 'Number of NOR Sectors: 0x8000\n' : cfw_compat ? 'Number of NAND Sectors: 0x80000 (0x77800 in dump)\n':'Number of eMMC Sectors: 0x80000 (0x77800 in dump)\n';
						helper.sysinfo.txt += 'Minimum Applicable Firmware Version: '+helper.minver+'\n';
						helper.sysinfo.txt += 'IDPS: '+idps+'\n';
						helper.sysinfo.txt += 'PSID: '+psid+'\n';
						helper.sysinfo.txt += 'Product Code: '+pcode+'\n';
						helper.sysinfo.txt += 'Product SubCode: '+pscode+'\n';
					}
					if(!cfw_compat){jstree.disable_node('flashbk');}
					var ros0_ref = '';
					var ros1_ref = '';
					var ros0_new = '';
					var ros1_new = '';
					var sha256_ref = '';
					var sha256_pending = false;
					this.refreshFM_node = function(cb){
						//alert('fmm refreshFM_node');
						sha256_pending = true;
						jstree.rename_node('info0','Calculating SHA256 checksum, please wait...');
						jstree.rename_node('info1','Calculating SHA256 checksum, please wait...');
						var sbuf = helper.sm.getBuffer();
						if(!sbuf){
							helper.sp.playNG();
							Logger.error('SHA256 Extraction failed. No buffer available.');
							toast('If the toolset keeps getting errors when allocating buffer memory, you should restart the console.','error',5);
							jQuery().toastmessage('removeToast', close_toast);
							jQuery('.preloader').removeClass('ui-helper-hidden').addClass('ui-helper-hidden');
							return;
						}
						var tl = helper.worker['fmm'].getTLS();
						if(!tl){
							Logger.error('SHA256 Extraction: TLS memory allocation failed!');
							toast('TLS memory allocation failed','error',5);
							return;
						}
						//alert('fmm ROSHashObject');
						var rosH = new ROSHashObject(so,{'dump_start':so.is_nor() ? 0x600: 0x400,'data_buffer':sbuf,'tls':tl});
						if(rosH.error.code>0){
							Logger.error('SHA256 Extraction: ROSHashObject creation failed!');
							toast('SHA256 Extraction failed','error',5);
							return;
						}
						//TO-DO:
						//Show spinner
						function sha256_cleanup(){
							//alert('sha256_cleanup');
							if(!helper.sysinfo.done){
								helper.sysinfo.done = true;
							}
							so.close();
							delete rosH;
							enable_GUI();
							jQuery().toastmessage('removeToast', close_toast);
							jQuery('.refresh-fm').removeClass('ui-state-disabled');
							jQuery('.preloader').removeClass('ui-helper-hidden').addClass('ui-helper-hidden');
						}
						function sha256_error(str){
							//alert(str);
							jstree.rename_node('info0','SHA256: Extraction Error');
							jstree.rename_node('info1','SHA256: Extraction Error');
							//jstree.rename_node('minver','Minimum Applicable Firmware Version: '+helper.minver);
							if(!helper.sysinfo.done){
								helper.sysinfo.txt += 'ROS SHA256: Extraction Error\n';
							}
							else{
								if(ros0_ref.length>0 && helper.sysinfo.txt.indexOf(ros0_ref)>=0){
									helper.sysinfo.txt = helper.sysinfo.txt.replace(ros0_ref,'Extraction Error');
								}
								if(ros1_ref.length>0 && helper.sysinfo.txt.indexOf(ros1_ref)>=0){
									helper.sysinfo.txt = helper.sysinfo.txt.replace(ros1_ref,'Extraction Error');
								}
							}
							jstree.open_all('flash');
							sha256_pending = false;
							sha256_cleanup();
						}
						ros0_ref = ros0_new;
						ros1_ref = ros1_new;
						helper.worker['fmm'].run(rosH.sfx[0],'ROS Data Extraction',function(){Logger.info('Extracting data from Flash Memory ROS regions');},function(){
								//alert('fmm ros data extraction');
								function checkArr(arr,val){
									var good=true;
									for(var st=0;st<arr.length;st++){
										if(helper.memory.upeek32(arr[st])===val){
											//alert('problem at index 0x'+st.toString(16)+' Offset: 0x'+arr[st].toString(16));
											good=false;
											break;
										}
									}
									return good;
								}
								if(!checkArr(rosH.rlen,0xFFFFFFFF)){
									sha256_error('ROS Extraction error');
									return;
								}
								else{
									//Logger.trace(rosH.log[0]);
									//jstree.rename_node('minver','Minimum Applicable Firmware Version: '+helper.minver);
								}
						});
						helper.worker['fmm'].run(rosH.sfx[1],'ROS1 Data hashing',function(){Logger.info('ROS1 Data hashing');},function(){
								//alert('fmm ros1 hashing');
								ros1_new = helper.memory.upeeks(rosH.hash_r1, 0x20, false).toUpperCase();
								if(ros1_new ==='FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF'){
									sha256_error('ROS 1 hashing error');
									return;
								}
								//Logger.trace(rosH.log[1]);
								//Logger.info('ROS 1 hash: '+ros1_new);
								jstree.rename_node('info1','SHA256: '+ ros1_new);
								if(!helper.sysinfo.done){
									helper.sysinfo.txt += 'ROS 1 hash: '+ros1_new+'\n';
								}
								else if(ros1_ref.length>0 && ros1_ref!==ros1_new){
									helper.sysinfo.txt = helper.sysinfo.txt.replace(ros1_ref, ros1_new);
								}
						});
						helper.worker['fmm'].run(rosH.sfx[2],'ROS0 Data hashing',function(){Logger.info('ROS0 Data hashing');},function(){
								//alert('fmm ros0 hashing');
								ros0_new = helper.memory.upeeks(rosH.hash_r0, 0x20, false).toUpperCase();
								if(ros0_new==='FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF'){
									sha256_error('ROS 0 hashing error');
									return;
								}
								//Logger.trace(rosH.log[2]);
								//Logger.info('ROS 0 hash: '+ros0_new);
								jstree.rename_node('info0','SHA256: '+ ros0_new);
								if(!helper.sysinfo.done){
									helper.sysinfo.txt += 'ROS 0 hash: '+ros0_new+'\n';
								}
								else if(ros0_ref.length>0 && ros0_ref!==ros0_new){
									helper.sysinfo.txt = helper.sysinfo.txt.replace(ros0_ref, ros0_new);
								}
								jstree.open_all('flash');
								sha256_pending = false;
								helper.sp.playOK();
								if(cb){cb(this.changedROS());}
								sha256_cleanup();
								Logger.info('ROS SHA256 checksums <br>ROS0 = '+ros0_new+'<br>ROS1 = '+ros1_new);
								
								// syscon eeprom read tests
								// var pm = getProductModeFlag();
								// Logger.info(pm.error===0 ? 'Product Mode Flag: 0x'+pm.flag : 'Product Mode Flag Read Error: 0x'+pm.error);
								// toast(pm.error===0 ? 'Product Mode Flag: 0x'+pm.flag : 'Product Mode Flag Read Error: 0x'+pm.error,pm.error===0 ?'notice':'warning',10);
								// var rm = getRecoverModeFlag();
								// Logger.info(rm.error===0 ? 'Recover Mode Flag: 0x'+rm.flag : 'Recover Mode Flag Read Error: 0x'+rm.error);
								// toast(rm.error===0 ? 'Recover Mode Flag: 0x'+rm.flag : 'Recover Mode Flag Read Error: 0x'+rm.error,rm.error===0 ?'notice':'warning',10);
								// //var sm = setRecoverFlag();
								// //Logger.info(sm===0 ? 'Recover Mode Flag Set to 0xFF' : 'Recover Mode Flag Set Error');
								// var fself = getFSELFFlag();
								// Logger.info(fself.error===0 ? 'FSELF Flag: 0x'+fself.flag : 'FSELF Flag Read Error: 0x'+fself.error);
								// toast(fself.error===0 ? 'FSELF Flag: 0x'+fself.flag : 'FSELF Flag Read Error: 0x'+fself.error,fself.error===0 ?'notice':'warning',10);
								// var bf = getBootFlag();
								// Logger.info(bf.error===0 ? 'Boot Flag: 0x'+bf.flag : 'Boot Flag Read Error: 0x'+bf.error);
								// toast(bf.error===0 ? 'Boot Flag: 0x'+bf.flag : 'Boot Flag Read Error: 0x'+bf.error,bf.error===0 ?'notice':'warning',10);
								// end syscon eeprom read tests

								
								jQuery('#fTree').mCustomScrollbar({
									axis:'y',
									theme: (Cookies.get('style')==='eggplant') ? 'light-thick' : 'dark-thick',
									advanced:{
										updateOnContentResize: true,
										updateOnImageLoad: true
									},
									keyboard: {enable:false},
									mouseWheel: {enable:false}
								});
						});
					};
					this.isSHA256Pending = function(){
						return sha256_pending;
					};
					this.checkFMSHA256 = function(){
						var fp_hashref = helper.sha256_loadedros;
						Logger.info('checkFMSHA256: Patch File Hash: 0x'+fp_hashref);
						Logger.info('checkFMSHA256: ROS0 Hash: 0x'+ros0_new);
						Logger.info('checkFMSHA256: ROS1 Hash: 0x'+ros1_new);
						return {'ros0':ros0_new === fp_hashref, 'ros1':ros1_new === fp_hashref};
					};
					this.changedROS = function(){
						return !(ros0_new === ros0_ref && ros1_new === ros1_ref);
					};
					var _storage = helper.worker['fmm'].getTLS();
					if(!_storage){
						Logger.error('SHA256 Extraction: TLS memory allocation failed!');
						toast('TLS memory allocation failed','error',5);
						return;
					}
					_storage=_storage.offset;
					helper.sc_storage={
						sysinfo:_storage,
						hwinfo:_storage+0x18,
						status:_storage+0x20,
						softid:_storage+0x24,
						pid_rom:_storage+0x2C,
						pid_ram:_storage+0x34,
						runtime:_storage+0x3C,
						bu_ct:_storage+0x40,
						sd_ct:_storage+0x44,
						err_code:[], 				// 0x80  bytes (0x20*4 bytes)
						err_time:[],				// 0x100 bytes (0x20*8 bytes)
						err_status:[],				// 0x80  bytes (0x20*4 bytes)
						err_rets:[], 				// 0x80  bytes (0x20*4 bytes)
						sysinfo_ret:_storage+0x2C8,
						hwinfo_ret:_storage+0x2CC,
						scversion_ret:_storage+0x2D0,
						becount_ret:_storage+0x2D4
					};
					var sc_sf=vsyscall32(helper.sys_sm_get_get_system_info,helper.sc_storage.sysinfo)+store_r3_word(helper.sc_storage.sysinfo_ret)
						+vsyscall32(helper.sys_sm_get_hw_config,helper.sc_storage.status,helper.sc_storage.hwinfo)+store_r3_word(helper.sc_storage.hwinfo_ret)
						+vsyscall32(helper.sys_sm_request_scversion,helper.sc_storage.softid,helper.sc_storage.pid_rom,helper.sc_storage.pid_ram)+store_r3_word(helper.sc_storage.scversion_ret)
						+vsyscall32(helper.sys_sm_request_be_count,helper.sc_storage.status,helper.sc_storage.runtime,helper.sc_storage.bu_ct,helper.sc_storage.sd_ct)+store_r3_word(helper.sc_storage.becount_ret);
					for(var err_i=0;err_i<0x20;err_i++){
						helper.sc_storage.err_code[err_i]=_storage+0x48+(err_i*4);
						helper.sc_storage.err_time[err_i]=_storage+0xC8+(err_i*8)+4;
						helper.sc_storage.err_status[err_i]=_storage+0x1C8+(err_i*4);
						helper.sc_storage.err_rets[err_i]=_storage+0x248+(err_i*4);
						sc_sf += vsyscall32(helper.sys_sm_request_error_log,err_i,helper.sc_storage.err_status[err_i],helper.sc_storage.err_code[err_i],helper.sc_storage.err_time[err_i])+store_r3_word(helper.sc_storage.err_rets[err_i]);
					}
					helper.worker['fmm'].run(sc_sf,'System Information extraction',
						function(){
							Logger.info('Extracting system info data');
							stdc.memset(helper.sc_storage.sysinfo,0,0x258);
						},function(){
							function check_err(addr,jobs){
								if(addr!== undefined && jobs!== undefined && parseInt(addr)===addr && addr>0 && jobs.length!== undefined && jobs.length>0){
									var _err=helper.memory.upeek32(addr);
									Logger.info('check_err: 0x'+_err.toString(16));
									if(_err!==0){
										//alert('check_err error 0x'+_err.toString(16)+' in '+jobs[0].job);
										var check_it=0;
										for(check_it=0;check_it<jobs.length;check_it++){
											Logger.error(jobs[check_it].job+' Error 0x'+_err.toString(16));
											jstree.rename_node(jobs[check_it].node, jobs[check_it].job+' Error 0x'+_err.toString(16));
											if(!helper.sysinfo.done){
												helper.sysinfo.txt += jobs[check_it].job+' Error 0x'+_err.toString(16)+'\n';
											}
										}
										return false;
									}
								}
								return true;
							}
							
							if(check_err(helper.sc_storage.sysinfo_ret,[{job:'Firmware Version Extraction',node:'firmware'},{job:'Platform ID Extraction',node:'platform'},{job:'Board ID Extraction',node:'board'}])){
								var scfw = helper.memory.upeek32(helper.sc_storage.sysinfo);
								var platform_id = helper.memory.upeeks(helper.sc_storage.sysinfo+0x8,8,true);
								var mbd = helper.mboard_table[platform_id] ? helper.mboard_table[platform_id] : 'unknown';
								var stbuild = helper.memory.upeek32(helper.sc_storage.sysinfo+0x10);
								var stfw = ((scfw >> 24) & 0xF).toString(16)+'.'+(((scfw & 0xFF000 )>> 12) & 0xFF).toString(16);
								jstree.rename_node('firmware','Firmware Version: '+stfw+' build '+stbuild);
								jstree.rename_node('platform','Platform ID: '+ platform_id);
								jstree.rename_node('board','Board ID: '+ mbd);
								if(!helper.sysinfo.done){
									helper.sysinfo.txt += 'Firmware Version: '+stfw+' build '+stbuild+'\n';
									helper.sysinfo.txt += 'Platform ID: '+platform_id+'\n';
									helper.sysinfo.txt += 'Board ID: '+mbd+'\n';
								}
							}
							if(check_err(helper.sc_storage.hwinfo_ret,[{job:'Hardware Config Extraction',node:'hwconfig'}])){
								var hwcfg=helper.memory.upeeks(helper.sc_storage.hwinfo,0x8,false).toUpperCase();
								jstree.rename_node('hwconfig','Hardware Config: '+hwcfg);
								if(!helper.sysinfo.done){
									helper.sysinfo.txt += 'Hardware Config: '+hwcfg+'\n';
								}
							}
							if(check_err(helper.sc_storage.scversion_ret,[{job:'Syscon Firmware Version Extraction',node:'scver'}])){
								Logger.info('scver: 0x'+helper.memory.upeeks(helper.sc_storage.softid,0x18,false));
								var scfrev=helper.memory.upeek32(helper.sc_storage.softid+4).toString(16).toUpperCase()+'.'+helper.memory.upeeks(helper.sc_storage.pid_rom,8,false).toUpperCase();
								var eepromrev=helper.memory.upeeks(helper.sc_storage.pid_ram,8,false).toUpperCase();
								jstree.rename_node('scver','Syscon Firmware Version: '+scfrev+' (EEPROM: '+eepromrev+')');
								if(!helper.sysinfo.done){
									helper.sysinfo.txt += 'Syscon Firmware Version: '+scfrev+' (EEPROM: '+eepromrev+')\n';
								}
							}
							if(check_err(helper.sc_storage.becount_ret,[{job:'Statistics Extraction',node:'onstats'}])){
								var _time = helper.memory.upeek32(helper.sc_storage.runtime);
								var _days = Math.floor(_time/86400);
								var _hours = Math.floor((_time-(_days*86400))/3600);
								var _min = Math.floor((_time-(_days*86400)-(_hours*3600))/60);
								var _s = _time-(_days*86400)-(_hours*3600)-(_min*60);
								var stat_bc = helper.memory.upeek32(helper.sc_storage.bu_ct).toString();
								var stat_sc = helper.memory.upeek32(helper.sc_storage.sd_ct).toString();
								jstree.rename_node('onstats','Statistics: Boot Count '+stat_bc+' - Shutdown Count '+stat_sc+' - Runtime  '+_days.toString()+'d '+_hours.toString()+'h '+ _min.toString()+'mn '+_s.toString()+'s');
								if(!helper.sysinfo.done){
									helper.sysinfo.txt += 'Statistics: Boot Count '+stat_bc+' - Shutdown Count '+stat_sc+' - Runtime  '+_days.toString()+'d '+_hours.toString()+'h '+ _min.toString()+'mn '+_s.toString()+'s\n';
								}
							}
							Logger.info('System info data extraction done');
							var err_node = jstree.get_node('errors');
							if(err_node){
								Logger.info('Extracting system error logs');
								for(var err_it=0;err_it<0x20;err_it++){
									var _err_ret=helper.memory.upeek32(helper.sc_storage.err_rets[err_it]);
									var _err_stat=helper.memory.upeek8(helper.sc_storage.err_status[err_it]);
									if(_err_ret!==0 || _err_stat!==0){
										var _err = _err_ret!==0 ? _err_ret : _err_stat;
										var _serr = _err_ret!==0 ? 'sc errors extraction log syscall error for entry ' : 'sc errors extraction log status error for entry ';
										Logger.error(_serr+' 0x'+err_it.toString(16)+' : 0x'+_err.toString(16));
										break;
									}
									//0x386D4380 = number of seconds between 1970 & 2000 (syscon keeps number of seconds since 2000 in 32bit which may be a problem in about 100 years)
									// JS date (like most systems) uses 1970 to start counter & uses milliseconds so we need to fix the syscon value

									var timepk = (helper.memory.upeek32(helper.sc_storage.err_time[err_it])+0x386D4380)*1000;
									//Logger.info('timepk in seconds 0x'+timepk.toString(16));
									var errcode = helper.memory.upeek32(helper.sc_storage.err_code[err_it]);
									if(errcode===0xFFFFFFFF){
										timepk = 0;
									}
									var ttmp = new Date(timepk);
									//Logger.info('JS time returned string '+ttmp.toUTCString());
									//Logger.info('JS local time returned string '+ttmp.toString());
									jstree.create_node(err_node,{ 'id' : 'error_'+err_it.toString(), 'type' : 'scerror', 'text' : 'Error '+err_it.toString()+': 0x'+errcode.toString32().toUpperCase()+' Time: '+ttmp.toUTCString() });
									if(!helper.sysinfo.done){
										helper.sysinfo.txt += 'Error '+err_it.toString()+': 0x'+errcode.toString32().toUpperCase()+' Time: '+ttmp.toUTCString()+'\n';
									}
								}
							}
							else{
								Logger.error('Missing errors tree node');
							}
							jstree.rename_node('errors','Errors ('+jstree.get_node('errors').children.length.toString()+')');
							if(!helper.sysinfo.done){
								helper.sysinfo.txt += 'Extracted SYSCON Errors ('+jstree.get_node('errors').children.length.toString()+')\n';
							}
							jstree.open_node('system');
							Logger.info('System error logs extraction done');
							//alert('System error logs extraction done');
							helper.sc_storage=null;
							sc_sf = null;
							//setTimeout(this.refreshFM_node,0);
					});
					this.refreshFM_node();
				};
				
				var cleanup = function(obj){
					pbfm1.ulog('Flash Memory Dump Operations Cleanup');
					var serr=so.close();
					if(serr!==0){
						pbfm1.ulog('Flash Memory Storage Object Close Error: 0x'+serr.toString(16));
					}
					var ferr= obj.f.close();
					delete obj.f;
					obj.f=null;
					if(ferr!==0){
						pbfm1.ulog('File Object Close error: 0x'+ferr.toString(16));
					}
					obj.d.log=null;
					obj.d.rret=null;
					obj.d.wret=null;
					obj.d.rlen=null;
					obj.d.wlen=null;
					obj.d.sfr=null;
					obj.d.sfw=null;
					delete obj.d;
					obj.d=null;
					obj=null;
				};
				var result = function(obj){
					helper.sp.playOK();
					cleanup(obj);
					setTimeout(function(){
						toast('Dump process completed successfully','success',5);
						pbfm1.updateProgressDialog({'gvalue':100,'glabel':'Created Dump File \''+obj.filename+'\'','istatus':'success-image'});
						toast('The validity of a dump should always be confirmed by a proper validator tool such as pyPS3checker','notice',5);
						//setTimeout(helper.sp.playOK,250);
					},100);
				};
				var failed = function(obj){
					helper.sp.playNG();
					pbfm1.ulog('Flash Memory Dump Process Error<br>'+obj.error.toString(16));
					pbfm1.updateStatusText(obj.status);
					pbfm1.updateProgressDialog({'dlabel':obj.error,'glabel':'Dump Operations Failure','dvalue':100,'gvalue':100,'istatus':'error-image'});
					toast('An error occurred during the Dump process. Check the log for details.','error',5);
					cleanup(obj);
				};
				var inProgress = function(obj){
					pbfm1.updateStatusText(obj.status);
					pbfm1.updateProgressDialog({'dlabel':obj.dlab,'glabel':obj.glab,'dvalue':obj.dval,'gvalue':obj.gval});
				};
				var deferred=null;
				
				var mt_dump = function(dump_object){
					if(!dump_object){return;}
					var _nor = so.is_nor();
					var start = new Date();
					pbfm1.ulog(start,true);
					Logger.warn('Dump start'+getElapsedTime(start));
					try{
						var idx = dump_object.file_path.lastIndexOf('/');
						var filename = dump_object.file_path.substr(idx+1,dump_object.file_path.length-idx-1);
						var szout = dump_object.save_offset>0 ? helper.patchfile_size.toString(16): (dump_object.sector_count*helper.sector_size).toString(16);
						var norout = _nor ?  'NOR': 'NAND';
						pbfm1.ulog('Dump Parameters:<br>Total Sector Count: 0x'+dump_object.sector_count.toString(16)+
						'<br>Dump Start Offset: 0x'+dump_object.dump_start.toString(16)+
						'<br>Dump File Path: '+dump_object.file_path+
						'<br>Dump File Size: 0x'+szout+' bytes'+
						'<br>Flash Memory Storage Object created'+
						'<br>Detected Type: '+norout
						);
						var f = new fileObject(dump_object.file_path,helper.fs_flag_create_rw);
						Logger.warn('fileObject created'+getElapsedTime(start));
						pbfm1.ulog(f.size>0 ? 'File IO Overwriting '+dump_object.file_path : 'File IO Creating '+dump_object.file_path );
						var d = new dumpObject(so,f,dump_object);
						Logger.warn('dumpObject '+getElapsedTime(start));
						var max_it = Math.floor(dump_object.sector_count/dump_object.nsec_iter);
						var rem_sec = dump_object.sector_count - (max_it*dump_object.nsec_iter);
						var inc = rem_sec===0 ? 100/max_it : max_it>0 ? 100/(max_it+1) : 100;
						var tsz_written=0
						var pbdetails = 0;
						var pbglobal = 0;
						var pblabdetails = 'Preparing Target File';
						var pblabglobal = 'Starting Dump Operations';
						deferred = jQuery.Deferred();
						deferred.promise().then(result,failed,inProgress);
						Logger.warn('Promise '+getElapsedTime(start));
						var sread = function(obj){
							if(deferred.state()!=='pending'){
								return;
							}
							else if(pbfm1.cancelled()){
								deferred.reject({'f':f,'d':d,'error':'Dump Operations Cancelled By User','status':getElapsedTime(start)});
								return;
							}
							pblabdetails = obj.index!==0 ? 'Saving Extracted Data to File' : 'Extracting Flash Memory Data' ;//
							pbdetails = 0;
							setTimeout(function(){
								deferred.notify({'glab': pblabglobal,'dlab': pblabdetails,'gval': pbglobal,'dval': pbdetails,'status':getElapsedTime(start)});
							},0);
							pbfm1.ulog('Flash Memory IO Current Sector: 0x'+(obj.index*obj.value + dump_object.dump_start).toString(16)+'<br>Flash Memory IO Reading 0x'+obj.value.toString(16)+' sectors');
							for(var t=0;t<d.rlen[obj.index].length;t++){
								var rlen = helper.memory.upeek32(d.rlen[obj.index][t]);
								var err = helper.memory.upeek32(d.rret[obj.index][t]);
								if(err!==0 || rlen === 0 || rlen > 0x800  ){
									deferred.reject({'f':f,'d':d,'error':err === 0xFFFFFFFF ?  'Thread Synchronization error' : err === 0 ? 'Invalid Flash Memory Read Length 0x'+rlen.toString(16) : 'Flash Memory Read Error 0x'+err.toString(16),'status':getElapsedTime(start)});
									return;
								}
							}
							helper.worker['fmm'].run(d.sfw[obj.index],'Writing Data to File',function(){Logger.info('Writing Data to File');},function(){check_write(obj);});
							return;
						};
						var check_write = function(obj){
							if(deferred.state()!=='pending'){
								return;
							}
							else if(pbfm1.cancelled()){
								deferred.reject({'f':f,'d':d,'error':'Dump Operations Cancelled by User','status':getElapsedTime(start)});
								return;
							}
							var err = helper.memory.upeek32(d.wret[obj.index]);
							var fnl = rem_sec > 0 ? max_it : max_it-1;
							var size = dump_object.save_offset!==0 ? helper.patchfile_size: obj.value*helper.sector_size;
							pblabdetails = obj.index!==fnl ? 'Extracting Data from Flash Memory':'Dump Operations Complete';//
							pbdetails = 100;
							pbglobal += inc;
							pbglobal = pbglobal===100 ? 99 : pbglobal;
							pbfm1.ulog('<br>'+new Date());
							var szw = helper.memory.upeek32(d.wlen[obj.index]+0x4);
							if(err!==0 || szw!==size){
								f.size += szw;
								var errstr = err > 0 ? ' 0x' + err.toString(16) : '';
								Logger.error('Dump Object mt_save error: '+ errstr + '<br>Dump Object mt_save Data Size:  0x'+szw.toString(16)+' bytes written to file - Expected: 0x'+size.toString(16)+' bytes');
								deferred.reject({'f':f,'d':d,'error':err === 0xFFFFFFFF ?  'Thread Synchronization error' : 'File Save IO Error 0x'+err.toString(16),'status':getElapsedTime(start)});
								return;
							}
							f.size += size;
							tsz_written +=parseFloat(Math.round((szw/0x100000) * 100) / 100);
							pblabglobal = 'Saved '+tsz_written.toString()+' Mb to \''+filename+'\'';//
							setTimeout(function(){
								deferred.notify({'glab': pblabglobal,'dlab': pblabdetails,'gval': pbglobal,'dval': pbdetails,'status':getElapsedTime(start)});
							},0);
							Logger.info(d.log[obj.index]);
							//Logger.trace(d.log[obj.index]);
							pbfm1.ulog('File IO Total Size Written to File '+tsz_written.toString()+'Mb');
							if(obj.index===fnl){
								Logger.warn('Dump Complete '+getElapsedTime(start));
								setTimeout(function(){
									pbfm1.ulog('Flash Memory successfully dumped in<br>'+dump_object.file_path);
									deferred.resolve({'f':f,'d':d,'filename':filename});
								},0);
							}
							else {
								
								var ix = obj.index+1;
								ix = ix < max_it ? ix : rem_sec > 0 && ix === max_it ? ix : 0;
								if(ix>0){
									helper.worker['fmm'].run(d.sfr[ix],'Reading Data from Flash Memory',function(){Logger.info('Reading Data from Flash Memory');},function(){
										sread({'index':ix,'value': ix < max_it ? dump_object.nsec_iter : rem_sec});
									});
								}
							}
						};
						helper.worker['fmm'].run(d.sfr[0],'Reading Data from Flash Memory',function(){Logger.info('Reading Data from Flash Memory');},function(){sread({'index':0,'value':max_it > 0 ? dump_object.nsec_iter : rem_sec > 0 ? rem_sec : 0});});
					}
					catch(e){
						Logger.error('<h2><b>JS Exception: </b></h2><br>'+e);
					}
				};
				var mt_patch = function(patch_object){
					if(!patch_object){return;}
					var _nor = so.is_nor();
					var start = new Date();
					pbfm1.ulog(start,true);
					pbfm1.updateProgressDialog({'dlabel':'Initializing..','glabel':'Flash Memory Patching','dvalue':0,'gvalue':0});
					try{
						var norout = _nor ?  'NOR': 'NAND';
						pbfm1.ulog('Patch Parameters:<br>Patch Total Sector Count: 0x'+patch_object.sector_count.toString(16)+
							'<br>Patch Start Offset: 0x'+patch_object.patch_start.toString(16)+
							'<br>Flash Memory Storage Object created'+
							'<br>Detected Type: '+norout
						);
						prepPatchFile(patch_object.data_buffer);
						if(helper.sha256_loadedros !== getSHA256hash(patch_object.data_buffer.offset+0x30, helper.patchfile_size)){
							//abort
							removePatch();
							pbfm1.ulog('Loaded Patch Validation Error<br>');
							pbfm1.updateStatusText('Loaded patch in-memory corruption detected');
							pbfm1.updateProgressDialog({'dlabel':'Please load the patch file again','glabel':'Patching Operations Failure','dvalue':100,'gvalue':100,'istatus':'error-image'});
							return;
						}
						var po = new patchObject(so,patch_object);
						var cleanup = function(){
							var serr=so.close();
							if(serr!==0){pbfm1.ulog('Flash Memory Storage Object Close Error: 0x'+serr.toString(16));}
							delete po;
							pbfm1.ulog('Flash Memory Patching Operations Cleanup');
						};
						var result = function(){
							helper.sp.playOK();
							pbfm1.ulog('Flash Memory successfully patched');
							cleanup();
							setTimeout(function(){
								toast('You can reboot your console.','success',5);
								pbfm1.updateProgressDialog({'gvalue':100,'glabel':'Patch applied successfully','istatus':'success-image'});
								
							},750);
							//setTimeout(helper.sp.playOK,1500);
						};
						var failed = function(o){
							helper.sp.playNG();
							removePatch();
							pbfm1.ulog('Flash Memory Patching Process Error<br>'+o.error.toString(16));
							pbfm1.updateStatusText(o.status);
							pbfm1.updateProgressDialog({'dlabel':o.error,'glabel':'Patching Operations Failure','dvalue':100,'gvalue':100,'istatus':'error-image'});
							cleanup();
							if(o.recalculateSHA===true){
								pbfm1.ulog('Checking for Flash Memory changes');
								ft1.refreshFM_node(function(changes){
									if(changes === true){
										pbfm1.ulog('Data was written to the Flash Memory. DO NOT REBOOT without fixing the ROS regions first.');
										toast('An error occurred during the patching process & data was written to the Flash Memory. DO NOT reboot the console with the Flash Memory in the current state. Check the log for details.','error',5);
										Logger.error('Patching failed and data was written to the Flash Memory. You must repair the damage. DO NOT REBOOT.');
									}
									else{
										pbfm1.ulog('No data was written to the Flash Memory.');
										Logger.warn('Patching failed but no data has been written to the Flash Memory.');
										toast('An error occurred during the patching process but no data has been written to the Flash Memory. It should be safe to reboot. Check the logs for details.','warning',5);
									}
								});
							}
						};
						var inProgress = function(o) {
							pbfm1.updateStatusText(o.status);
							pbfm1.updateProgressDialog({'dlabel':o.dlab,'glabel':o.glab,'dvalue':o.dval,'gvalue':o.gval});
						};
						deferred = jQuery.Deferred();
						deferred.promise().then(result,failed,inProgress);
						var patchROS = function(idx){
							var pbdetails = 0;
							var pbglobal = idx*40 + 20;
							var pblabdetails = 'Patching Flash Memory Region ROS'+idx.toString();
							var pblabglobal = 'Flash Memory Patch Operations';
							if(deferred.state()!=='pending'){
								return;
							}
							var cp = 0;
							for(var i = po.ret[idx].length-1;i >= 0;i--){
								if(helper.memory.upeek32(po.ret[idx][i])=== 0xFFFFFFFF){ret++;}
							}
							if(cp>0){
								deferred.reject({'error':'Flash Memory Write Operations failed','status':getElapsedTime(start),'recalculateSHA': cp===po.ret.length ? false : true});
								return;
							}
							var offt = idx===0 ? patch_object.data_buffer.offset + patch_object.offset_data.ros0 : patch_object.data_buffer.offset + patch_object.offset_data.ros1;
							Logger.info('Patching ROS'+idx.toString()+' with buffered data at 0x'+offt.toString(16));
							//setTimeout(function(){
								deferred.notify({'glab': pblabglobal,'dlab': pblabdetails,'gval': pbglobal,'dval': pbdetails,'status':getElapsedTime(start)});
							//},0);
							var cnt=0;
							for(var t = po.wlen[idx].length-1;t >= 0;t--){
								var wlen = helper.memory.upeek32(po.wlen[idx][t]);
								if(wlen === 0 || wlen===0xFFFFFFFF){cnt++;}
							}
							if(cnt>0){
								deferred.reject({'error':'Flash Memory Write Operations failed some sectors without errors','status':getElapsedTime(start),'recalculateSHA': cnt===po.wlen.length ? false : true});
								return;
							}
							//pbglobal = idx*40 + 20;
							//setTimeout(function(){
								deferred.notify({'glab': pblabglobal,'dlab': pblabdetails,'gval': pbglobal,'dval': pbdetails,'status':getElapsedTime(start)});
							//},0);
							pbfm1.ulog('ROS'+idx.toString()+' patch operations completed without errors');
							Logger.info(po.log[idx]);
							//Logger.trace(po.log[idx]);
							if(idx === 0){
								if(!_nor){helper.memory.upoke32(patch_object.data_buffer.offset+0x14,0);}
								helper.worker['fmm'].run(po.sfp[1],'Patching ROS1 Data',function(){Logger.info('Patching ROS1 Data');},function(){patchROS(1);});
							}
							else{
								pbfm1.ulog('Calculating SHA256 checksum for ROS banks 0 & 1');
								Logger.info('Calculating ROS banks SHA256 hashes');
								pblabglobal = 'Flash Memory Post Patching Data Verifications';
								pblabdetails = 'Calculating SHA256 checksums';
								pbdetails = 0;
								deferred.notify({'status': getElapsedTime(start),'glab': pblabglobal,'dlab': pblabdetails,'gval': pbglobal,'dval': pbdetails});
								//removePatch();
								ft1.refreshFM_node();
								pbglobal = 80;
								pbdetails = 50;
								function checkSHA256(){
									deferred.notify({'status': getElapsedTime(start),'glab': pblabglobal,'dlab': pblabdetails,'gval': pbglobal,'dval': pbdetails});
									if(ft1.isSHA256Pending()=== true){pbglobal = 90;pbdetails = 75;setTimeout(checkSHA256,250);return;}
									setTimeout(function(){
										var status = ft1.checkFMSHA256();
										if(status.ros0 && status.ros1){
											pbfm1.ulog('Patch applied on ROS bank 0: YES<br>Patch applied on ROS bank 1: YES');
											pbglobal = 99;
											pbdetails = 100;
											pblabdetails = 'Idle';
											deferred.notify({'status': getElapsedTime(start),'glab': pblabglobal,'dlab': pblabdetails,'gval': pbglobal,'dval': pbdetails});
											setTimeout(function(){
												deferred.resolve();
											},200);
										}
										else{
											var r0 = status.ros0 ? 'MATCH' : 'NO MATCH';
											var r1 = status.ros1 ? 'MATCH' : 'NO MATCH';
											Logger.info('SHA256 checksum for ROS bank 0 vs patch file checksum : '+r0+'<br>SHA256 checksum for ROS bank 1 vs patch file checksum : '+r1);
											var u_r0 = status.ros0 ? 'YES' : 'NO';
											var u_r1 = status.ros1 ? 'YES' : 'NO';
											pbfm1.ulog('Patch applied on ROS bank 0: '+u_r0+'<br>Patch applied on ROS bank 1: '+u_r1);
											deferred.reject({'status':getElapsedTime(start),'error':'SHA256 verification failed.','recalculateSHA':false});
										}
										removePatch();
									},250);
								}
								checkSHA256();
							}
						};
						helper.worker['fmm'].run(po.sfp[0],'Patching ROS0 Data',function(){Logger.info('Patching ROS0 Data');},function(){patchROS(0);});
					}
					catch(e){
						Logger.error('<h2><b>JS Exception: </b></h2><br>'+e);
					}
				};
				
				if(helper.worker['fmm']){
					setTimeout(function(){
						ft1 = new fTree(toast('Extracting Data from the Flash Memory. Please wait...','warning',120));
						initPatchData();
						helper.deletePatchData();
						jQuery(document).tooltip();
					},1500);
				}
				else {Logger.error('SM Worker Thread creation failed');}
				</script>
			