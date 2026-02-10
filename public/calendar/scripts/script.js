function confirmDelete(remove)
{
    if (confirm('Are you sure?'))
    {
        window.location.href = 'eventList.php?remove=' + remove;
    }
}

function deleteNews(remove)
{
    if (confirm('Are you sure?'))
    {
        window.location.href = 'newsList.php?remove=' + remove;
    }
}

function validate(form) {
    fail = validateDate(form.eventStart.value, "Start");
    fail += validateDate(form.eventEnd.value, "End");
    fail += validateTitle(form.title.value);
    if (fail == "") return true
    else { alert(fail); return false }
}

function validateTitle(field)
{
    return (field == "" ? "Title was not supplied.\n" : "");
}

function validateDate(field, text)
{
    if (field == "")
    {
        return "Start Date is missing.\n";
    }
    else
    {
        return (moment(field, 'YYYY-MM-DD HH:mm', true).isValid() ? "" : "Start Date is in an invalid format YYYY-MM-DD HH:mm");
    }
}


    
