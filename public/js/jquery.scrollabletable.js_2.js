/**
 * Scrollable Tables for IE and Mozilla
 *
 * This jQuery function will set the height/width and add scroll bars if required. For
 * internet explorer it breaks apart to table into 1-3 tables (header, body, footer). In mozilla
 * it adds a scroll bar to the tbody element.
 *
 * Usage:
 * $("table").scrollable();
 * $("table").scrollable({tableWidth:915, tableHeight:400});
 *
 * Your table needs to be using the following format for this to work well:
 *
 * <table>
 *  <thead>
 *      <th>...</th>
 *  </thead>
 *  <tbody>
 *      <td>...</td>
 *  </tbody>
 * </table>
 *
 *  This plugin is loosly based on: http://www.webtoolkit.info/scrollable-html-table-plugin-for-jquery.html
 *
 *  This library is free software; you can redistribute     
 *  it and/or modify it under the terms of the GNU          
 *  Lesser General Public License as published by the       
 *  Free Software Foundation; either version 2.1 of the     
 *  License, or (at your option) any later version.         
 *                                                          
 *  This library is distributed in the hope that it will    
 *  be useful, but WITHOUT ANY WARRANTY; without even the   
 *  implied warranty of MERCHANTABILITY or FITNESS FOR A    
 *  PARTICULAR PURPOSE. See the GNU Lesser General Public   
 *  License for more details.                               
 *                                                          
 *  You should have received a copy of the GNU Lesser       
 *  General Public License along with this library;         
 *  Inc., 59 Temple Place, Suite 330, Boston,               
 *  MA 02111-1307 USA
 *
 *  For full license details see: http://www.gnu.org/licenses/gpl.txt
 */

(function($){
    $.fn.scrollable = function(options) {

        var opts = $.extend({}, $.fn.scrollable.defaults, options);

        return this.each(function() {

            var scrolTable = $(this);

            // TODO: Move this into default options (if possible)
            // Set the default table width/height (if not provided)
            opts.tableWidth = opts.tableWidth ? opts.tableWidth : scrolTable.width();
            opts.tableHeight = opts.tableHeight ? opts.tableHeight : scrolTable.height();

            if (jQuery.browser.msie) {

                // Create container for table;
                var containerDiv = $("<div></div>");
                containerDiv.insertBefore(scrolTable);

                // Find tfoot and thead if exists
                var tableFoot = scrolTable.find("tfoot");
                var tableHead = scrolTable.find("thead");

                // If we have a header, move it to a new div
                if (tableHead) {
                    var headerTable = scrolTable.clone(true);
                    headerTable.find("tbody").remove();
                    headerTable.find("tfoot").remove();
                    headerTable.css("margin", "0px");
                    headerTable.width(opts.tableWidth - opts.scrollWidth);

                    // Remove thead from table and add clone to container
                    scrolTable.find("thead").remove();
                    containerDiv.append(headerTable);
                }

                // Create a div to "scroll" table in IE
                var tableDiv = $("<div></div>");
                tableDiv.append(scrolTable);
                tableDiv.width(opts.tableWidth);
                tableDiv.css("overflow", "auto");
                tableDiv.height(opts.tableHeight);
                scrolTable.css("margin", "0px");
                scrolTable.width(opts.tableWidth - opts.scrollWidth);
                containerDiv.append(tableDiv);

                // If we have a footer, move it to a new div
                if (tableFoot) {
                    var footerTable = scrolTable.clone(true);
                    footerTable.find("thead").remove();
                    footerTable.find("tbody").remove();
                    footerTable.css("margin", "0px");
                    footerTable.width(opts.tableWidth - opts.scrollWidth);

                    // Remove tfoot from table and add clone to container
                    scrolTable.find("tfoot").remove();
                    containerDiv.append(footerTable);
                }

                // Sync table column widths
                $.scrollable.syncWidths(containerDiv, opts);

            } else if (jQuery.browser.mozilla) {

                var theadHeight = scrolTable.find("thead").height() ? scrolTable.find("thead").height() : 0;
                var tfootHeight = scrolTable.find("tfoot").height() ? scrolTable.find("tfoot").height() : 0;
                var tbodyHeight = scrolTable.find("tbody").height();

                // Calculate height to determine if we need scroll bars
                var calulatedHeight = opts.tableHeight - (theadHeight + tfootHeight);

                // Turn on vertical scroll bar if required
                if (tbodyHeight >= calulatedHeight) {
                    scrolTable.find("tbody").css("overflow", "-moz-scrollbars-vertical");
                } else {
                    scrolTable.find("tbody").css("overflow", "-moz-scrollbars-none");
                }

                var cellSpacing = (scrolTable.attr("offsetHeight") - (tbodyHeight + theadHeight + tfootHeight)) / 4;
                var tBodyHeight = (opts.tableHeight - (theadHeight + cellSpacing * 2) - (tfootHeight + cellSpacing * 2));
                var tBodyWidth = opts.tableWidth;

                scrolTable.css("width", opts.tableWidth + 'px');
                scrolTable.find("tbody").css("height",tBodyHeight  + 'px');
                scrolTable.find("tbody").css("width",tBodyWidth  + 'px');

            }
        });
    };

    $.scrollable = {
        syncWidths : function(container, opts) {
            if (jQuery.browser.msie) {

                var thead = container.find("thead");
                var tbody = container.find("tbody");
                var tfoot = container.find("tfoot");

                // Determine what we should synchronize widths with?
                var synchList = container.find(opts.synchWidthsWith + " tr:first td");
                if(opts.synchWidthsWith != "tbody") {
                    synchList = container.find(opts.synchWidthsWith + " tr:first th");
                }

                // If we have columns to synch with, then sychronize
                if(synchList.size() > 0) {

                    // Set the width on all columns in tbody (ensures consistency when using filtered table plugin)
                    $.each(tbody.find("tr"), function(i,tr) {
                        $.each($(tr).find("td"), function(i,td) {
                            var column = $(synchList[i]);
                            $(td).width(column.width());
                            $(td).removeAttr("width");
                        });
                    });

                    //*********************************
                    // Because of "non breaking text" we now copy the body column sizes back to the header/footer
                    //*********************************

                    if(thead) {
                        var theadColumns = thead.find("tr:first th");

                        // Remove width/style from header and set word break
                        $.each(theadColumns, function(i,th) {
                            $(th).css("wordBreak","break-all");
                            $(th).removeAttr("width");
                            $(th).css("width", "");
                        });

                        // Set the widths on the header columns instead
                        $.each(tbody.find("tr:first td"), function(i,td) {
                            $(theadColumns[i]).width($(td).width());
                        });
                    }

                    // Set table foot column widths
                    if(tfoot) {
                        var tfootColumns = tfoot.find("tr:first th");

                        // Remove width/style from footer and set word break
                        $.each(tfootColumns, function(i,th) {
                            $(th).css("wordBreak","break-all");
                            $(th).removeAttr("width");
                            $(th).css("width", "");
                        });

                        // Set the widths on the footer columns instead
                        $.each(tbody.find("tr:first td"), function(i,td) {
                            $(tfootColumns[i]).width($(td).width());
                        });
                    }
                }
            }
        }
    };
    
    $.fn.scrollable.defaults = {
        synchWidthsWith: "tbody",
        scrollWidth: 17
    };
})(jQuery);

