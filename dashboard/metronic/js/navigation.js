




function setHeaderMenu(thisSite)
{
    var items = {
                index:{
                    title: 'Dashboard',
                    key: 'index',
                    category: 'index',
                    subcategory: '',
                    subpath: '',
                    active: true,
                    display: true
                },
                vendor_delivery_summary: {
                    title: 'Vendor Delivery Summary',
                    key: 'vendor_delivery_summary',
                    category: 'reports',
                    subcategory: 'vendor',
                    subpath: '',
                    active: true,
                    display: true
                },
                vendor_avg_delivery_time_by_country: {
                    title: 'Vendor Avg Delivery Time By Country',
                    key: 'vendor_avg_delivery_time_by_country',
                    category: 'reports',
                    subcategory: 'vendor',
                    subpath: '',
                    active: true,
                    display: true
                },
                wizard_orders_fulfillment_items_by_vendor_id_and_delivery_status: {
                    title: 'Wizard',
                    key: 'wizard_orders_fulfillment_items_by_vendor_id_and_delivery_status',
                    category: 'reports',
                    subcategory: 'wizard',
                    subpath: '',
                    active: true,
                    display: true
                },
                tracking_fulfillment: {
                    title: 'Fulfillment',
                    key: 'tracking_fulfillment',
                    category: 'tracking',
                    subcategory: 'tracking',
                    subpath: '',
                    active: true,
                    display: true
                },
                tracking_unfulfillment: {
                    title: 'Unfulfillment',
                    key: 'tracking_fulfillment',
                    category: 'tracking',
                    subcategory: 'tracking',
                    subpath: '',
                    active: true,
                    display: true
                },
                tracking_files: {
                    title: 'Display Files',
                    key: 'tracking_files',
                    category: 'tracking',
                    subcategory: 'tracking',
                    subpath: '',
                    active: true,
                    display: true
                }
    };
    
    var categories = {
                index: {
                    title: 'Dashboard',
                    key: 'index',
                    subcategories: false
                },
                reports: {
                    title: 'Reports',
                    key: 'reports',
                    subcategories: true
                },
                tracking: {
                    title: 'Tracking',
                    key: 'tracking',
                    subcategories: true
                }
    }
    
    var subcategories = {
                vendor: {
                    title: 'Vendors',
                    key: 'vendor',
                    parent: 'reports',
                    items: true
                },
                refund: {
                    title: 'Refunds',
                    key: 'refund',
                    parent: 'reports',
                    items: false
                },
                wizard: {
                    title: 'Wizard',
                    key: 'wizard',
                    parent: 'reports',
                    items: true
                },
                tracking: {
                    title: 'Tracking',
                    key: 'tracking',
                    parent: 'tracking',
                    items: true
                },
    }
    
    var prefix = function(site){
        if(site == 'index'){
            var x = ''
        }else{
            var x = '../../'};
        return x;
    };
    
    function buildLink(site,p){
        var result      = prefix(p);
        var category    = items[site]['category'];
        var subcategory = items[site]['subcategory'];
        
        if(category && subcategory){result += category + '/' +  subcategory + '/';};
        result = result + site + '.html';        
        return result;
    }
    
    var menuItems = function(subcategory){
        var result = '';
         $.each(items,function(key,value){
            if(value.subcategory == subcategory && value.display){
                varlink = '#';
                if(items[thisSite]['active']){
                    link = buildLink(value.key,thisSite);
                }
                var active = '';
                if(items[thisSite]['category'] == value.key){
                    active = ' m-menu__item--active';
                }
                
                result += '\n\
                                                                                    <!-- BEGIN: ' + value.title + ' -->\n\
														<ul class="m-menu__inner' + active + '">\n\
															<li class="m-menu__item "  data-redirect="true" aria-haspopup="true">\n\
																<a  href="' + link + '" class="m-menu__link ">\n\
																	<span class="m-menu__link-text">\n\
																		' + value.title + '\n\
																	</span>\n\
																</a>\n\
															</li>\n\
														</ul>\n\
                            ';
                 
            }
         });
         
         return result;
    };
    
    var menuSubcategory = function(category){
        var result = '\n\
						<!-- BEGIN: Subcategory -->\n\
									<div class="m-menu__submenu  m-menu__submenu--fixed m-menu__submenu--left" style="width:800px">\n\
											<span class="m-menu__arrow m-menu__arrow--adjust"></span>\n\
											<div class="m-menu__subnav">\n\
												<ul class="m-menu__content">';
        $.each(subcategories,function(key,value){
            
            if(category == value.parent){
                if(value.items){
                    var items = menuItems(value.key);
                } else {
                    var items ='';
                }

                result += '\n\
                                                    <!-- BEGIN: ' + value.title + ' -->\n\
                                                                                                            <li class="m-menu__item">\n\
                                                                                                                    <h3 class="m-menu__heading m-menu__toggle">\n\
                                                                                                                            <span class="m-menu__link-text">\n\
                                                                                                                                    ' + value.title + '\n\
                                                                                                                            </span>\n\
                                                                                                                            <i class="m-menu__ver-arrow la la-angle-right"></i>\n\
                                                                                                                    </h3>\n\
                                                                                                                        ' + items + '\n\
                                                                                                            </li>\n\
                                                                    <!-- END: ' + value.title + ' -->';
            }
        });
        
        result += '\n\
                                                                                                    </ul>\n\
											</div>\n\
                                                                        </div>\n\
								<!-- END: Subcategory -->';
        return result;
        
    };
    
    
    var menuCategory = function(){
        var result = '';
        
        $.each(categories,function(key,value){
            
            if(value.subcategories){
                var link = '#';
                var toggle = '  m-menu__toggle';
                var angleDown = '<i class="m-menu__hor-arrow la la-angle-down"></i>';
                var subcategories = menuSubcategory(value.key);
            } else {
                var link = buildLink(value.key,thisSite);
                var toggle = '';
                var angleDown = '';
                var subcategories = '';
            }
            
            var active = '';
            if(items[thisSite]['category'] == value.key){
                active = ' m-menu__item--active';
            }
            
            result += '\n\
						<!-- BEGIN: ' + value.title + ' -->\n\
									<li class="m-menu__item  m-menu__item--submenu m-menu__item--rel' + active + '"  data-menu-submenu-toggle="click"  data-redirect="true" aria-haspopup="true">\n\
										<a  href="' + link + '" class="m-menu__link' + toggle + '">\n\
											<span class="m-menu__item-here"></span>\n\
											<span class="m-menu__link-text">\n\
												' + value.title + '\n\
											</span>\n\
											<i class="m-menu__ver-arrow la la-angle-right"></i>\n\
                                                                                        ' + angleDown + '\n\
										</a>\n\
                                                                                ' + subcategories + '\n\
									</li>\n\
								<!-- END: ' + value.title + ' -->';
        });
        
        return result;
    }
    //alert(menuCategory());
    var menu = '\n\
								<ul class="m-menu__nav  m-menu__nav--submenu-arrow ">\n\
                                                                    ' + menuCategory() + '\n\
								</ul>';
    
    $('#m_header_menu').append(menu);
    
    
    
}