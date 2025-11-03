// JScript File

// This function determines which browser we're using to return an element
function getElement(strID)
{
    if( document.getElementById ) // this is the way the standards work    
        elem = document.getElementById( strID );  
    else if( document.all ) // this is the way old msie versions work      
        elem = document.all[strID];  
    else if( document.layers ) // this is the way nn4 works    
        elem = document.layers[strID]; 
        
    return elem;
}

// Creates the menu inside the menu DIV tag in the template
function CreateMenu() {
    var strMenu;
    strMenu = '<nav class="navbar navbar-expand-lg">\n'
    strMenu += '<button class="navbar-toggler" type="button" data-toggle="collapse" data-target=".navbar-collapse" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">\n'
    strMenu += '   <span class="navbar-toggler-icon" >Menu</span>\n'
    strMenu += '</button>\n'
    strMenu += '<div class="collapse navbar-collapse" id="navbarNav">\n'
    strMenu += '   <ul class="navbar-nav">\n'
    strMenu += '	   <li class="nav-item"><a class="nav-link" id="l_Index" href="index.html">Welcome</a></li>\n'
    strMenu += '	   <li class="nav-item"><a class="nav-link" id="l_rules" href="rules.htm">Range Rules</a ></li >\n'
    strMenu += '	   <li class="nav-item dropdown">\n'
    strMenu += '		   <a class="nav-link dropdown-toggle" id="HomeDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Club Information</a>\n'
    strMenu += '		   <div class="dropdown-menu" aria-labelledby="HomeDropdownMenuLink">\n'
    strMenu += '			   <a class="dropdown-item" id="l_history" href="history.htm">Club history</a>\n'
    strMenu += '			   <a class="dropdown-item" id="l_location" href="location.htm">Location</a>\n'
    strMenu += '			   <a class="dropdown-item" id="l_schedule" href="schedule.htm">Schedule</a>\n'
    strMenu += '			   <a class="dropdown-item" id="l_faq" href="faq.htm">FAQ </a>\n'
    strMenu += '		   </div>\n'
    strMenu += '	   </li>\n'
    strMenu += '	   <li class="nav-item dropdown">\n'
    strMenu += '		   <a class="nav-link dropdown-toggle" id="MembershipDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Membership</a>\n'
    strMenu += '		   <div class="dropdown-menu" aria-labelledby="MembershipDropdownMenuLink">\n'
    strMenu += '			   <a class="dropdown-item" id="l_membership" href="membership.htm">Info</a>\n'
    strMenu += '			   <a class="dropdown-item" id="l_newmember" href="newmember.htm">New Members</a>\n'
    strMenu += '			   <a class="dropdown-item" id="l_renewal" href="renewal.htm">Renewals</a>\n'
    strMenu += '		   </div>\n'
    strMenu += '	   </li>\n'
    strMenu += '	   <li class="nav-item dropdown">\n'
    strMenu += '		   <a class="nav-link dropdown-toggle" id="MembershipDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Links</a>\n'
    strMenu += '		   <div class="dropdown-menu" aria-labelledby="MembershipDropdownMenuLink">\n'
    strMenu += '			   <a class="dropdown-item" id="l_shows" href="shows.htm">Shows & Auctions </a>\n'
    strMenu += '			   <a class="dropdown-item" id="l_links" href="links.htm">Shooting links</a>\n'
    strMenu += '		   </div>\n'
    strMenu += '	   </li>\n'
    strMenu += '	   <li class="nav-item"><a class=" nav-link" id="l_events" href="calendar.htm">CALENDAR</a></li>\n'
    strMenu += '	   <li class="nav-item"><a class=" nav-link" id="l_news" href="news.htm">News</a></li>\n'
    strMenu += '	   <li class="nav-item dropdown">\n'
    strMenu += '		   <a class="nav-link dropdown-toggle" id="FacilitiesDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Facilities</a>\n'
    strMenu += '		   <div class="dropdown-menu" aria-labelledby="FacilitiesDropdownMenuLink">\n'
    strMenu += '			   <a class="dropdown-item" id="l_indoors" href="indoors.htm">Indoors </a>\n'
    strMenu += '			   <a class="dropdown-item" id="l_outdoors" href="outdoors.htm">Outdoor </a>\n'
    strMenu += '			   <a class="dropdown-item" id="l_courses" href="courses.htm">Courses </a>\n'
    strMenu += '			   <a class="dropdown-item" id="l_property" href="propertymap.htm">Property map </a>\n'
    strMenu += '		   </div>\n'
    strMenu += '	   </li>\n'
    strMenu += '	   <li class="nav-item"><a class="nav-link" id="l_sections" href="sections.htm">Sections</a></li>'
    strMenu += '	   <li class="nav-item"><a class="nav-link" id="l_contact" href="contact.htm">Contact us </a></li>\n'
    strMenu += '   </ul>\n'
    strMenu += '</div>\n'
    strMenu += '</nav>\n'

    // Add the menu to the DOM
    $("#menu").append(strMenu);
}
   
$(function () {
    CreateMenu();
    $($("#pageRef").val()).addClass('active');
});