
				<div id='memedit'>
					<h2 align='right' class='tab-header'>Userland Memory Editor <span class='header-tiny-text'>v1.1</span></h2>
					<div class='me-sizer'>
						<table id ='mebox' class='window'>
							<tbody class='ui-corner-all'>
								<tr class='window-header ui-widget-header'>
									<th class='logoptions window-header ui-widget-header '><div class='dir-table'><span class='dir-left'>
									<div class='spinner min-width-550' tabindex='0' >
									<input id='spinner-text' class='spinner' value=''readonly />
									<span class='spinner-go-button '>
										<button id='btn_spinner' class='gui-item ui-button ui-corner-all ui-state-disabled'>Go to</button>
									</span>
									</div></span><span id='spanmode' class='dir-ume' title='Disable UME Strict Mode to browse userland memory without restrictions.Keep in mind however that any attempt to access unallocated memory addresses with UME Strict Mode off will crash the console.'><div class='switch-wrapper  pointer' tabindex='0'><input type='checkbox' name='mode' id='memhexmode' value='true' checked='true' ></div></span></div></th>
								</tr>
								<tr class='window-content ui-widget-content'>
									<td align='justify' class='window-content ui-widget-content'>
										<div id='hexTable' class='hexeditor'>
											<table id='xtable' class='x-table ui-widget ui-corner-all'>
												<colgroup>
													<col class='cell-offset' />
													<col class='cell-value' />
													<col class='cell-value' />
													<col class='cell-value' />
													<col class='cell-value' />
													<col class='cell-ascii' />
												</colgroup>
												<thead class='table-header ui-widget-header ui-corner-all'>
													<tr class='row-header'>
														<th class='cell-header ui-corner-tl'>Offset</th>
														<th class='cell-header '>0</th>
														<th class='cell-header'>+0x4</th>
														<th class='cell-header '>+0x8</th>
														<th class='cell-header '>+0xC</th>
														<th class='cell-header ui-corner-tr'>ASCII</th>
													</tr>
												</thead>
												<tbody>
												</tbody>
											</table>
										</div>
									</td>
								</tr>
								<tr class='window-bottom ui-widget-header '>
									<td class='window-bottom btlogoptions ui-widget-header '>
										<div class='dir-table ui-corner-bottom '>
											<span class='dir-left min-width-210'>
												<button id='btn2bis' class='gui-item ui-button ui-corner-all' title = ''>
													<i class='fa fa-fast-backward fa-fw'></i>
												</button>
												<button id='btn2' class='gui-item ui-button ui-corner-all' title = ''>
													<i class='fa fa-step-backward fa-fw'></i>
												</button>
											</span>
											<span class='dir-center min-width-210'>
												<button id='btn1' class='gui-item ui-button ui-corner-all' title = 'Go to the memory offset provided in the textbox'>Go to Offset</button>
												<span class='top-2px'>
													<input align='center' size='10' type='text' title = 'Enter a 32 bit userland memory address' id='offset' class='gui-item ui-widget ui-corner-all'/>
												</span>
											</span>
											<span class='dir-right min-width-210'>
												<button id='btn3' class='gui-item ui-button ui-corner-all' title = ''>
													<i class='fa fa-step-forward fa-fw'></i>
												</button>
												<button id='btn3bis' class='gui-item ui-button ui-corner-all' title = ''>
													<i class='fa fa-fast-forward fa-fw'></i>
												</button>
											</span>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					
					</div>
				</div>
				<script>
					jQuery('.preloader').removeClass('ui-helper-hidden');
					disable_GUI();
					var current = {'id':'current','name':'Current Segment','start':0,'end':0,'offset':0};
					var rec_offset=0;
					var current_index =0;
					var ranges = [{'id':'vsh_text','name':'VSH Text Segment','start':0x10000,'end':helper.vshgadgets_box.vshdata_seg,'offset':0},
								{'id':'vsh_data','name':'VSH Data Segment','start':helper.vshgadgets_box.vshdata_seg,'end':0x800000,'offset':0},
								{'id':'browser_container','name':'PS3 Browser Memory Container','start':0x80000000,'end':0x90000000,'offset':0}
					];
					jQuery.widget('ui.textSpinner', jQuery.ui.spinner, {
						options: {
							wrap: true
						},
						_parse: function (value) {
							if ((value === '') || isNaN(value)) {
								value = this.options.values.indexOf(value);
								if (value === -1) {
									value = 0;
								}
							}
							if (value < 0) {
								value = this.options.wrap ? (this.options.values.length -1) : 0;
							} else if (value >= this.options.values.length) {
								value = this.options.wrap ? 0 : (this.options.values.length - 1);
							}
							return value;
						},
						_format: function (value) {
							return this.options.values[value];
						},
						_adjustValue: function (value) {
							if (value < 0) {
								value = this.options.wrap ? (this.options.values.length - 1) : 0;
							} else if (value >= this.options.values.length) {
								value = this.options.wrap ? 0 : (this.options.values.length - 1);
							}
							return value;
						}
					}); 
					var createSpinner = function(){
						var arrSpin = [];
						for(var i=0;i<ranges.length;i++){
							arrSpin[i]=ranges[i].name;
						}
						//Crazy hack to get rid of automatic focusing nightmare on ps3. np on other browsers of course lol
						jQuery('#spinner-text').textSpinner({
							values: arrSpin,
							spin: function(event,ui) {
								// index of spin entry in ui.value
								// text value of spin entry in this.values[ui.value]
								event.stopPropagation();
								var bt = jQuery('#btn_spinner');
								if(current===ranges[ui.value]){
									bt.removeClass('ui-state-disabled').addClass('ui-state-disabled');
								}
								else{
									bt.removeClass('ui-state-disabled');
								}
								bt.off('click');
								bt.on('click',function(e){
									current = ranges[ui.value];
									current.offset = ranges[ui.value].start;
									jQuery('#memedit' ).trigger( 'refreshEvent',[ toast('Refreshing data','warning',4)]);
									bt.removeClass('ui-state-disabled').addClass('ui-state-disabled');
									event.stopPropagation();
								});
							}
						});
						jQuery('#btn_spinner').off('keyUp');
						jQuery('.ui-spinner-button').off('keyUp');
						jQuery('.ui-spinner-input').off('keyDown');
						jQuery('.ui-spinner-input').off('blur');
						jQuery('.ui-spinner-input').on('focus',function(){
							jQuery(this).blur();
							jQuery('.ui-spinner-up').focus();
						});
						jQuery('.ui-spinner-up').on('keyUp',function(){
							jQuery('#spinner-text').textSpinner('stepUp',1);
							jQuery(this).focus();
						});
						jQuery('.ui-spinner-down').on('keyUp',function(){
							jQuery('#spinner-text').textSpinner('stepDown',1);
							jQuery(this).focus();
						});
					};
					var mapModules = function(close_toast){
						var mods = 0;
						var ret = 0;
						var bs =  helper.heap.store(0x600);
						var module_list = bs;
						var idlist = bs+0x18;
						var module_info = bs+0x218;
						var file_name = bs+0x260;
						var seg_info = bs+0x360;
						var s_module_info = bs+0x5E0;
						helper.memory.upokes(module_list,'0000000000000020000000000000008000000000'+idlist.toString32());
						helper.memory.upokes(s_module_info,'0000000000000010'+module_info.toString32());
						helper.memory.upokes(module_info,'0000000000000048000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000'+file_name.toString32()+'00000100'+seg_info.toString32()+'00000008');
						ret = helper.rop.rrun(syscall32(helper.sys_prx_get_module_list,2,module_list,0));
						if(ret===0){
							var count = helper.memory.upeek32(module_list+0x10);
							var id_peek = helper.memory.upeeks(idlist,count*4);
							function extract(from, size, peek,s,f,seg){
								var cnt = size-from<10? size-from : 10;
								for(var i=from;i<from+cnt;i++){
									var id = parseInt('0x'+peek.substr(i*0x8,0x8),16);
									if(id <= 0){continue;}
									helper.rop.run(syscall32(helper.sys_prx_get_module_info,id,0,s));
										var plugin_name = helper.memory.upeeks(f,0xFF,true);
										for(var j=0;j<2;j++){
											var speek = helper.memory.upeeks(seg+j*0x28,0x28);
											var base = parseInt('0x'+speek.substr(0x8,0x8),16);
											var t = j>0 ? 'Data' : 'Text';
											mods++;
											ranges.push({'name':plugin_name+' Module '+t+' Segment','start':base,'end':base+parseInt('0x'+speek.substr(0x28,0x8),16),'offset':0});
										}
								}
								if(from+cnt<size){
									setTimeout(function(){
										extract(from+cnt, count, peek,s,f,seg);
									},600);
								}
								else{
									helper.heap.free([bs]);
									Logger.info('mapModules: Number of sprx module segments mapped '+mods.toString());
									jQuery('#spinner-text' ).textSpinner( 'destroy' );
									createSpinner();
									jQuery().toastmessage('removeToast', close_toast);
									jQuery('.preloader').removeClass('ui-helper-hidden').addClass('ui-helper-hidden');
									helper.sp.playOK();
									return;
								}
							}
							extract(0,count,id_peek,s_module_info,file_name,seg_info);
						}
						else{
							helper.sp.playNG();
							Logger.error('mapModules: sys_prx_get_module_list error 0x'+ret.toString(16));
						}
					};
					var isInCurrentRange=function (off){
						if(off>=current.start && off < current.end){return true;}
						else{return false;}
					};
					var isValid=function (off){
						if(helper.me_usermode>0){return true;}
						for(var i=0;i<ranges.length;i++){
							if(off>=ranges[i].start && off < ranges[i].end){return true;}
						}
						return false;
					};
					var findRangeKey=function (off){
						for(var i=0;i<ranges.length;i++){
							if(off>=ranges[i].start && off < ranges[i].end){return ranges[i].id;}
						}
						return helper.me_usermode>0?{'start':0x0,'end':0xFFFFFFFF}:null;
					};
					var findRangeIndex=function (off){
						for(var i=0;i<ranges.length;i++){
							if(off>=ranges[i].start && off < ranges[i].end){return i;}
						}
						return -1;
					};
					function toAsciiStr(str){
						var ret='';
						for(var i=0;i<str.length/2;i++){
							var tval = parseInt('0x'+str.substr(i*2,2),16)&0xFF;
							ret += tval===0 ? '.': String.fromCharCode(tval);
						}
						return ret;
					}
					function updateMemory(e){
						var data = e.data;
						var ipt = jQuery('#'+data.offset.toString(16));
						ipt.removeClass('ui-state-error');
						ipt.off('change');
						ipt.off('focusout');
						ipt.off('focusin');
						var ival = ('00000000'+ipt.val().toUpperCase()).slice(-8);
						var val = parseInt(ival,16);
						var sval = val.toString32().toUpperCase();
						if(sval===ival && ival.length<=8){
							Logger.info('memedit: Updating 4 bytes at offset 0x'+data.offset.toString(16)+' with 32 bit value: 0x'+sval);
							helper.memory.upoke32(data.offset,val);
							data.value=sval;
							var ofst = data.offset.toString32();
							ofst=ofst.substr(0,ofst.length-1)+'0';
							jQuery('.ascii_'+ofst).text(toAsciiStr(helper.memory.upeeks(parseInt(ofst,16),0x10).toUpperCase()));
						}
						ipt.val(data.value);
						ipt.on('focusout',data,cancel);
						ipt.on('focusin',data,clean);
						ipt.on('change',data,updateMemory);
					}
					function cancel(e){
						var ipt = jQuery('#'+e.data.offset.toString(16));
						ipt.off('focusout');
						ipt.off('focusin');
						var pk = helper.memory.upeek32(e.data.offset).toString32().toUpperCase();
						if(pk===e.data.value){
							changeValue(e.data.offset,e.data.value);
							ipt.on('focusout',e.data,cancel);
							ipt.on('focusin',e.data,clean);
						}
						else{
							changeValue(e.data.offset,'undefined',pk);
							ipt.removeClass('ui-state-error').addClass('ui-state-error');
							ipt.parent().children().css({'height':'22px'});
							ipt.attr('title','Value out of synchronisation, the browser main thread or another userland thread is preventing us from getting a reliable reading at this offset.');
							jQuery(document).tooltip();
						}
					}
					function clean(e){
						changeValue(e.data.offset,'',e.data.value);
					}
					function changeValue(offset,valin,valout){
						var ipt = jQuery('#'+offset.toString(16));
						ipt.off('change');
						ipt.val(valin);
						ipt.on('change',{'offset':offset,'value':valout ? valout : valin},updateMemory);	
					}
					function convertToAscii(str){
						var ascii='';
						for(var i=0;i<str.length;i++){
							ascii += str.charCodeAt(i).toString8();
						}
						return ascii;
					}
					function convertfromAscii(str){
						var ret='';
						var i=0;
						while(i<str.length/2){
							ret += String.fromCharCode(parseInt(str.substr(i*2,2),16));
							i++;
						}
						return ret;
					}
					function addHexTable(offset) {	
						if(!current){Logger.error('addHexTable: Unexpected Range error');return;}
						jQuery('.x-table input').remove();
						jQuery('.x-table td').remove();
						jQuery('.x-table tr:not(.row-header)').remove();
						var arr = [];
						var i=0;
						var cnt=0;
						var table=document.getElementById('xtable');
						for(i=0;i<0x40;i++){
							if(isValid(offset+i*0x4)){cnt++;}
							if(cnt===1){rec_offset=offset+i*0x4;}
						}
						var pad='';
						for(i=0;i<0x40-cnt;i++){
							pad+='3F3F3F3F';
						}
						var speek = helper.memory.upeeks(rec_offset,cnt*0x4).toUpperCase();
						speek=rec_offset===offset ? speek+pad:pad+speek ;
						var end = rec_offset + cnt*0x4;
						for(i=0;i<16;i++){
							var row = table.insertRow( table.rows.length);
							arr[i] = [];
							var t1 = i===15 ? ' ui-corner-bl' : '';
							var t2 = i===15 ? ' ui-corner-br' : '';
							row.className = i===15 ? 'cell-row ui-corner-bottom' : '';
							var off = rec_offset+i*0x10;
							arr[i][0]=row.insertCell(0);
							arr[i][0].className = 'cell-header'+t1;
							arr[i][0].id='x'+off.toString32();
							arr[i][0].innerHTML= '0x'+off.toString32();
							arr[i][1]=row.insertCell(1);
							var i_1 = document.createElement('input');
							var test = offset+i*0x10 >= rec_offset && offset+i*0x10 < end;
							i_1.type='text';
							i_1.maxlength=9;
							i_1.size=16;
							i_1.className= test ? 'txt-center cell-data' : 'txt-center cell-data ui-state-disabled';
							i_1.id = off.toString(16);
							i_1.value = test ? speek.substr(0x20*i,8) : 'undefined';
							jQuery(arr[i][1]).append(i_1);
							var c_1 = jQuery('#'+i_1.id);
							c_1.on('focusin',{offset:off,value:i_1.value},clean);
							c_1.on('focusout',{offset:off,value:i_1.value},cancel);
							c_1.on('change',{offset:off,value:i_1.value},updateMemory);
							arr[i][2]=row.insertCell(2);
							var i_2 = document.createElement('input');
							test = offset+4+i*0x10 >= rec_offset && offset+4+i*0x10 < end;
							i_2.type='text';
							i_2.maxlength=9;
							i_2.size=16;
							i_2.className=test ? 'txt-center cell-data' : 'txt-center cell-data ui-state-disabled';
							i_2.id = (off+4).toString(16);
							i_2.value = test ? speek.substr(0x20*i+8,8) : 'undefined';
							jQuery(arr[i][2]).append(i_2);
							var c_2 = jQuery('#'+i_2.id);
							c_2.on('focusin',{offset:off+4,value:i_2.value},clean);
							c_2.on('focusout',{offset:off+4,value:i_2.value},cancel);
							c_2.on('change',{offset:off+4,value:i_2.value},updateMemory);
							arr[i][3]=row.insertCell(3);
							var i_3 = document.createElement('input');
							test = offset+8+i*0x10 >= rec_offset && offset+8+i*0x10 < end;
							i_3.type='text';
							i_3.maxlength=9;
							i_3.size=16;
							i_3.className=test ? 'txt-center cell-data' : 'txt-center cell-data ui-state-disabled';
							i_3.id = (off+8).toString(16);
							i_3.value = test ? speek.substr(0x20*i+16,8) : 'undefined';
							jQuery(arr[i][3]).append(i_3);
							var c_3 = jQuery('#'+i_3.id);
							c_3.on('focusin',{offset:off+8,value:i_3.value},clean);
							c_3.on('focusout',{offset:off+8,value:i_3.value},cancel);
							c_3.on('change',{offset:off+8,value:i_3.value},updateMemory);
							arr[i][4]=row.insertCell(4);
							var i_4 = document.createElement('input');
							test = offset+0xC+i*0x10 >= rec_offset && offset+0xC+i*0x10 < end;
							i_4.type='text';
							i_4.maxlength=9;
							i_4.size=16;
							i_4.className=test ? 'txt-center cell-data' : 'txt-center cell-data ui-state-disabled';
							i_4.id = (off+0xC).toString(16);
							i_4.value = test ? speek.substr(0x20*i+24,8) : 'undefined';
							jQuery(arr[i][4]).append(i_4);
							var c_4 = jQuery('#'+i_4.id);
							c_4.on('focusin',{offset:off+0xC,value:i_4.value},clean);
							c_4.on('focusout',{offset:off+0xC,value:i_4.value},cancel);
							c_4.on('change',{offset:off+0xC,value:i_4.value},updateMemory);
							arr[i][5]=row.insertCell(5);
							arr[i][5].className = 'cell-header'+t2;
							arr[i][5].innerHTML= '<div class=\'txt-center'+t2+' ascii ascii_'+off.toString32()+'\'>'+toAsciiStr(speek.substr(0x20*i,0x20))+'</div>';
						}
						arr=[];
						document.getElementById('offset').value = '0x'+rec_offset.toString(16).toUpperCase();
						document.getElementById('btn1').onclick = function(){
							var val = parseInt(document.getElementById('offset').value, 16);
							if(isNaN(val)===false){
								if(isValid(val)){
									current.offset = val;
									jQuery('#memedit' ).trigger( 'refreshEvent',[ toast('Refreshing data','warning',4)]);
								}
								else{
									alert('0x'+val.toString(16).toUpperCase()+' is not located in an accessible memory range');
									return;
								}
							}
							else{
								alert(document.getElementById('offset').value+' is not a valid 32 bit address');
							}
						};
						var b2 = document.getElementById('btn2');
						var cn2 = isValid(rec_offset-0x100) ? '': 'ui-state-disabled';
						b2.title = 'Go to offset 0x'+(rec_offset-0x100).toString(16).toUpperCase();
						b2.onclick = function(){
							current.offset = rec_offset-0x100;
							jQuery('#memedit' ).trigger( 'refreshEvent',[ toast('Refreshing data','warning',4)]);
						};
						
						var b2b = document.getElementById('btn2bis');
						var cn2b = isValid(rec_offset-0x1000) ? '': 'ui-state-disabled';
						b2b.title = 'Go to offset 0x'+(rec_offset-0x1000).toString(16).toUpperCase();
						b2b.onclick = function(){
							current.offset = rec_offset-0x1000;
							jQuery('#memedit' ).trigger( 'refreshEvent',[ toast('Refreshing data','warning',4)]);
						};
						
						var b3 = document.getElementById('btn3');
						var cn3 = isValid(rec_offset+0x100) ? '': 'ui-state-disabled';
						b3.title = 'Go to offset 0x'+(rec_offset+0x100).toString(16).toUpperCase();
						b3.onclick = function(){
							current.offset = rec_offset+0x100;
							jQuery('#memedit' ).trigger( 'refreshEvent',[ toast('Refreshing data','warning',4)]);
						};
						
						var b3b = document.getElementById('btn3bis');
						var cn3b = isValid(rec_offset+0x1000) ? '':'ui-state-disabled';
						b3b.title = 'Go to offset 0x'+(rec_offset+0x1000).toString(16).toUpperCase();
						b3b.onclick = function(){
							current.offset = rec_offset+0x1000;
							jQuery('#memedit' ).trigger( 'refreshEvent',[ toast('Refreshing data','warning',4)]);
						};
						var idx = findRangeIndex(rec_offset);
						if(idx>=0){
							current = ranges[idx];
							jQuery( '#spinner-text' ).textSpinner( 'value', ranges[idx].name );
						}
						if(current.name.indexOf('Text Segment')>0 && helper.me_usermode===0){
							jQuery('.cell-data').removeClass('ui-state-disabled').addClass('ui-state-disabled');
						}
						current.offset=offset;
						Logger.info('memedit: Created Memory Hex Table offset 0x'+rec_offset.toString(16).toUpperCase());
						jQuery('#memedit' ).off( 'refreshEvent');
						enable_GUI();
						jQuery(b2).removeClass('gui-disabled ui-state-disabled').addClass(cn2);
						jQuery(b2b).removeClass('gui-disabled ui-state-disabled').addClass(cn2b);
						jQuery(b3).removeClass('gui-disabled ui-state-disabled').addClass(cn3);
						jQuery(b3b).removeClass('gui-disabled ui-state-disabled').addClass(cn3b);
						jQuery('#btn_spinner').removeClass('ui-state-disabled').addClass(rec_offset===current.start ? 'ui-state-disabled':'');
						jQuery('#memedit' ).on( 'refreshEvent', function( event,tost ) {
							disable_GUI();
							setTimeout(function(){
								addHexTable(current.offset);
								jQuery('.refresh-me').removeClass('ui-state-disabled');
								jQuery().toastmessage('removeToast', tost);
							},0);
						});
						setTimeout(function(){
							jQuery(document).tooltip('disable');
							jQuery(document).tooltip();
							jQuery(document).tooltip('enable');
						},200);
					};
					try{
						var init_ume=false;
						jQuery('#memhexmode').switchButton({
							labels_placement: 'left',
							clear: false,
							on_label: 'UME Strict Mode ON ',
							off_label: 'UME Strict Mode OFF',
							on_callback: function (){
								jQuery(document).tooltip('disable');
								helper.me_usermode = 0;
								if(init_ume===true){
									jQuery('#memedit' ).trigger( 'refreshEvent',[ toast('Refreshing data','warning',4)]);
								}
								init_ume=true;
								
								jQuery(document).tooltip('enable');
							},
							off_callback: function(){
								jQuery(document).tooltip('disable');
								function confirmMode(){
									helper.me_usermode = 1;
									jQuery('#memedit' ).trigger( 'refreshEvent',[ toast('Refreshing data','warning',4)]);
									jQuery(document).tooltip('enable');
								}
								confirmDialog('Disabling Memory Editor Strict Mode is not recommended.<br><br>This tool will crash the ps3 if you attempt to access unallocated memory areas.','Are you sure you want to continue?',confirmMode,null,function(ck){jQuery('#memhexmode').switchButton('option','checked', ck);jQuery(document).tooltip('enable');},true);//jQuery('#memhex').tooltip();
							}
						});
						createSpinner();
						current = ranges[1];
						jQuery('#btn_spinner').on('click',function(e){
							current = ranges[1];
							current.offset = current.start;
							jQuery('#memedit' ).trigger( 'refreshEvent',[ toast('Refreshing data','warning',4)]);
							jQuery('#btn_spinner').removeClass('ui-state-disabled').addClass('ui-state-disabled');
						});
						jQuery( '#spinner-text' ).textSpinner( 'value', ranges[1].name );
						current.offset = current.start;
						addHexTable(current.start);
						var calc_toast = toast('Mapping sprx modules segments. Please wait...','warning',120);
						setTimeout(function(){
							mapModules(calc_toast);
							enable_GUI();
						},2200);
					}
					catch(e){
						Logger.error('<h2><b>JS Exception: '+e+'</b></h2><br>');
					}
				</script>
			