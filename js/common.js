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

