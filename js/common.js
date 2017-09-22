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
    strMenu = '<ul> \n';
    strMenu += '    <li><a id="l_Index" href="index.html" >Welcome</a></li> \n';
    strMenu += '    <li><a id="l_history" href="history.htm" >Club history</a></li> \n';
    strMenu += '    <li><a id="l_membership" href="membership.htm" >Membership info</a></li> \n';
    strMenu += '    <li><a id="l_forms" href="forms.htm" >Membership forms</a></li> \n';
    strMenu += '    <li><a id="l_schedule" href="schedule.htm" >Schedule</a></li> \n';
    strMenu += '    <li><a id="l_location" href="location.htm" >Location</a></li> \n';
    strMenu += '    <li><a id="l_events" href="calendar.htm" >CALENDAR</a></li> \n';
    strMenu += '    <li><a id="l_news" href="news.htm" >News  </a></li> \n';
    strMenu += '    <li><a id="l_shows" href="shows.htm" >Shows & Auctions </a></li> \n';
    strMenu += '    <li><a id="l_faq" href="faq.htm" >FAQ </a></li> \n';
    strMenu += '    <li><a id="l_contact" href="contact.htm" >Contact us </a></li> \n';
    strMenu += '    <li class="panelList" id="panFacilities"><img src="images/index-expand.jpg" height="11" alt="" border="0" />Facilities</li> \n';
    strMenu += '    <li style="display:none;"><div class="ListPanel">     \n';
    strMenu += '        <a id="l_indoors" href="indoors.htm" >Indoors </a><br /> \n';
    strMenu += '        <a id="l_outdoors" href="outdoors.htm" >Outdoor </a><br /> \n';
    strMenu += '        <a id="l_courses" href="courses.htm" >Courses </a><br /> \n';
    strMenu += '        <a id="l_property" href="propertymap.htm" >Property map </a> \n';
    strMenu += '    </div></li> \n';
    strMenu += '    <li class="panelList" id="panSections"><img src="images/index-expand.jpg" height="11" alt="" border="0" />Sections</li> \n';
    strMenu += '    <li style="display:none;"><div class="ListPanel"> \n';
    strMenu += '        <a id="l_junior" href="junior.htm" >Junior program </a><br /> \n';
    strMenu += '        <a id="l_archery" href="archery.htm" >Archery </a><br /> \n';
    strMenu += '        <a id="l_smallbore" href="smallbore.htm" >Smallbore rifles </a><br /> \n';
    strMenu += '        <a id="l_handgun" href="handgun.htm" >Handguns </a><br /> \n';
    strMenu += '    </div></li> \n';
    strMenu += '    <li><a href="board/index.php" target="new">PFGA Message Board</a></li> \n';
    strMenu += '    <li><a id="l_rules" href="rules.htm" >Range safety</a></li> \n';
    strMenu += '    <li><a id="l_links" href="links.htm" >Shooting links</a></li> \n';
    strMenu += '</ul>  \n';

    // Add the menu to the DOM
    $("#menu").append(strMenu);
    // Toggle the sub-menus
    $(".panelList").toggle(function(){
        // Show sub menu and flip the arrow down
         $(this).next().show('fast');
         $(this).children('img').attr("src","images/index-collapse.jpg");
       },function(){
        // Hide the sub menu and flip the arrow up
         $(this).next().hide('fast');
         $(this).children('img').attr("src","images/index-expand.jpg");
       });
}

// init function loads the data into the proper panes        
function init(){
    $("#header").load("header.htm");
    CreateMenu(); 
    $($("#pageRef").val()).css('color','blue');
   
    switch ($("#panelRef").val())
    {
        case 'Events':
            togglePanels("#panEvents");
            break;
        case 'Facilities':
            togglePanels("#panFacilities");
            break;
        case 'Sections':
            togglePanels("#panSections");
            break;
        default:
            
            
            break;
    }
};

//Changes the panels
function togglePanels(panelRef)
{
    //Hide Panels
    $("#panEvents").next().hide();
    $("#panEvents").children('img').attr("src","images/index-expand.jpg");
    $("#panFacilities").next().hide();
    $("#panFacilities").children('img').attr("src","images/index-expand.jpg");
    $("#panSections").next().hide();
    $("#panSections").children('img').attr("src","images/index-expand.jpg");
    
    $(panelRef).next().show();
    $(panelRef).children('img').attr("src","images/index-collapse.jpg");
    
}
   
$(document).ready(init);