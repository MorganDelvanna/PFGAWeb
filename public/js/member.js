const general = 350
const senior = 320
const junior = 250
const halfGeneral = 250
const halfSenior = 230
const halfJunior = 180
const initiation = 75
const extraCards = 25
const family = 20

function gatherFamily() {
    $('[name^="familyMembers"]').each(function(){
        let currentRow = $(this).closest(".row");
        let firstName = currentRow.find(".famName").val();
        let lastName = currentRow.find(".famLast").val();
        let pal = currentRow.find(".famPAL").val();
        let expiry = currentRow.find(".famExpiry").val();
        let dob = currentRow.find('.famDOB').val();

        let extra = `${firstName} ${lastName} DOB: ${dob} PAL: ${pal} ${expiry}`;
        $(this).val(extra);
    });
}

function gatherClubs() {
    $('[name^="otherClubs"]').each(function(){
        let currentRow = $(this).closest(".row");
        let name = currentRow.find(".otherClub").val();
        let city = currentRow.find(".otherCity").val();
        let from = currentRow.find(".otherFrom").val();
        let to = currentRow.find(".otherTo").val();

        let club = `${name} ${city} ${from} - ${to}`;
        $(this).val(club);
    });
}

function gatherCourses(){
    $('[name^="courses"]').each(function(){
        let currentRow = $(this).closest(".row");
        let name = currentRow.find(".courseDesc").val();
        let trainer = currentRow.find(".courseTrainer").val();
        let location = currentRow.find(".courseLocation").val();
        let courseDate = currentRow.find(".courseDate").val();

        let course = `${name} ${trainer} ${location} ${courseDate}`;
        $(this).val(course);
    });
}

function recalc() {
    let selectedValue = $('input[name="applicationType"]:checked').val();
    let totalInitiation;
    switch (selectedValue) {
        case "new":
        case "half":
            totalInitiation = initiation;
            break;
        case "renew":
            totalInitiation = 0;
            break;
    }
    let totalFee;
    let selectedFee = $('input[name="membershipFee"]:checked').val();
    switch (true) {
        case (selectedFee == "general" && selectedValue == "new"):
        case (selectedFee == "general" && selectedValue == "renew"):
            totalFee = general;
            break;
        case (selectedFee == "general" && selectedValue == "half"):
        case (selectedFee == "archery" && selectedValue == "half"):
        case (selectedFee == "archery" && selectedValue == "renew"):
            totalFee = halfGeneral;
            break;
        case (selectedFee == "senior" && selectedValue == "new"):
        case (selectedFee == "senior" && selectedValue == "renew"):
            totalFee = senior;
            break;
        case (selectedFee == "senior" && selectedValue == "half"):
            totalFee = halfSenior;
            break;
        case (selectedFee == "junior" && selectedValue == "new"):
        case (selectedFee == "junior" && selectedValue == "renew"):
            totalFee = junior;
            break;
        case (selectedFee == "junior" && selectedValue == "half"):
            totalFee = halfJunior;
            break;
        default: 
            totalfee = 0;
    }

    let totalFam = parseInt($('#family').val()) * family;
    let totalExtra = parseInt($('#extra').val()) * extraCards;

    $('#total').text(totalInitiation + totalFee + totalFam + totalExtra);
}

var applicationTypeChange = function() {
    let selectedValue = $('input[name="applicationType"]:checked').val();
    switch (selectedValue) {
        case "new":
            if (!$('label.new').first().hasClass('required')) {
                $('label.new').addClass('required');
                $('input.new').attr('required');
            }
            $('#cardCell').hide();
            $('#cardLabel').removeClass('required');
            $('#card').removeAttr('required');
            $('.newOnly').show();
            if ($('#archeryBtn').is(':checked')) {
                $('#generalBtn').prop("checked", true);
                $('[name="membershipFee"]').trigger('change');
            }
            $('#archeryBtn').hide();
            $('#initiationFee').text(`$${initiation}`);
            break;
        case "half":
            if (!$('label.new').first().hasClass('required')) {
                $('label.new').addClass('required');
                $('input.new').attr('required');
            }
            $('#cardLabel').removeClass('required');
            $('#card').removeAttr('required');
            $('.newOnly').show();
            $('#cardCell').hide();
            $('#archeryBtn').show();
            $('#initiationFee').text(`$${initiation}`);
            break;
        case "renew":
            $('label.new').removeClass('required');
            $('input.new').removeAttr('required');
            $('#cardCell').show();
            $('#cardLabel').addClass('required');
            $('#card').attr('required');
            $('.newOnly').hide();
            $('#initiationFee').text(`$0`);
            $('#archeryBtn').show();
            break;
    }
    recalc();
}

$(function(){
    $('.initiationFee').text(`$${initiation}`);    
    $('#initiationFee').text(`$${initiation}`); 
    $('#generalFee').text(`$${general}`);
    $('.generalFee').text(`$${general}`);
    $('.generalHalf').text(`$${halfGeneral} (New Members Only)`);
    $('.seniorFee').text(`$${senior}`);
    $('.seniorHalf').text(`$${halfSenior}`);
    $('.juniorFee').text(`$${junior}`);
    $('.juniorHalf').text(`$${halfJunior}`);
    $('.familyFee').text(`$${family} each`);
    $('.extraFee').text(`$${extraCards} each`);
    $('#homephone').inputmask("999-999-9999");
    $('#cellphone').inputmask("999-999-9999");

    var $inputs = $('input[name=homephone],input[name=cellphone]');
    $inputs.on('input', function () {
        // Set the required property of the other input to false if this input is not empty.
        $inputs.not(this).prop('required', !$(this).val().length);
    });
    

    let date = new Date();
    if (date.getMonth() >=7 && date.getMonth() <= 10) {
        $('#memberYear').text(`October 1st ${date.getFullYear()} - September 30th ${date.getFullYear() + 1}`);
    } else {
        $('#memberYear').text(`October 1st ${date.getFullYear() - 1} - September 30th ${date.getFullYear()}`);
    }

    if (date.getMonth() >=2 && date.getMonth() <= 6) {
        $('#halfColumn').show();
        $('#halfSpan').show();
    } else {
        $('#halfColumn').hide();
        $('#halfSpan').hide();
    }


    $('#addFamily').on('click', function(){
        let template = $('#familyTemplate').html();
        $('.familyRow:last').after(template);
        $('.btnDeleteFam').on('click', function(){
            $(this).closest(".familyRow").remove();
            let famNum = parseInt($('#family').val());
            if (famNum > 0) {
                $('#family').val(famNum - 1);
            }            
        });       
        let famNum = parseInt($('#family').val());
        $('#family').val(famNum + 1);
        recalc();
    });

    $('#addClub').on('click', function(){
        let template = $('#clubTemplate').html();
        $('.clubRow').after(template);
        $('.btnDeleteClub').on('click', function(){
            $(this).closest(".clubRow").remove();
        });
        $('.otherFrom').inputmask("99/99");
        $('.otherTo').inputmask("99/99");
    });

    $('#addCourse').on("click", function(){
        let template = $('#courseTemplate').html();
        $('.courseRow:last').after(template);
        $('.btnDeleteCourse').on("click", function(){
            $(this).closest(".courseRow").remove();
        });
        $('.courseDate').inputmask("99/99");
    });    

    $('[name="applicationType"]').on("change", applicationTypeChange);

    $('[name="palType"]').on('change', function(){
        let selectedValue = $('[name="palType"]:checked').val();
        let PALNum = $('#PALNum');
        let palExpiry = $('#palExpiry');
       
        switch(selectedValue){
            case "pal":
            case "rpal":
                if(!PALNum.hasClass('required')){
                    PALNum.attr('required');
                    palExpiry.attr('required');
                    $('label.pal').addClass('required');
                }        
                break;
            case "noPal":
                PALNum.removeAttr('required');
                palExpiry.removeAttr('required');
                $('label.pal').removeClass('required');
        }
    });

    $('[name="membershipFee"]').on("change", function(){
        let selectedValue = $('input[name="membershipFee"]:checked').val();
        let selectedType = $('input[name="applicationType"]:checked').val();
        switch (true) {
            case ((selectedValue == "general") && (selectedType == "new")):
            case ((selectedValue == "general") && (selectedType == "renew")):
                $('#generalFee').text(`$${general}`);
                $('#seniorFee').text("");
                $('#juniorFee').text("");
                $('#archeryFee').text("");
                break;
            case ((selectedValue == "general") && (selectedType == "half")):
                $('#generalFee').text(`$${halfGeneral}`);
                $('#seniorFee').text("");                
                $('#juniorFee').text("");
                $('#archeryFee').text("");
                break;
            case ((selectedValue == "senior") && (selectedType == "new")):
            case ((selectedValue == "senior") && (selectedType == "renew")):
                $('#seniorFee').text(`$${senior}`);
                $('#generalFee').text("");                
                $('#juniorFee').text("");
                $('#archeryFee').text("");
                break;
            case ((selectedValue == "senior") && (selectedType == "half")):
                $('#seniorFee').text(`$${halfSenior}`);
                $('#generalFee').text("");
                $('#juniorFee').text("");
                $('#archeryFee').text("");
                break;
            case ((selectedValue == "junior") && (selectedType == "new")):
            case ((selectedValue == "junior") && (selectedType == "renew")):
                $('#seniorFee').text("");
                $('#generalFee').text("");                
                $('#juniorFee').text(`$${junior}`);
                $('#archeryFee').text("");
                break;
            case ((selectedValue == "junior") && (selectedType == "half")):
                $('#seniorFee').text("");
                $('#generalFee').text("");
                $('#juniorFee').text(`$${halfJunior}`);
                $('#archeryFee').text("");
                break;
            case ((selectedValue == "archery") && (selectedType == "half")):
            case ((selectedValue == "archery") && (selectedType == "renew")):
                $('#seniorFee').text("");
                $('#generalFee').text("");                
                $('#juniorFee').text("");
                $('#archeryFee').text(`$${halfGeneral}`);
                break;
        }
        recalc();
    });

    $('#family').on("change", function() { recalc() });
    $('#extra').on("change", function() { recalc() });
    recalc();

    $.validator.addMethod("familyTest", function(value, element){
        return $('.famName').length == value;  
    }, "The number of family members does not match the number being paid for");
    
    $('#btnSubmit').on("click", function(){
        gatherFamily();
        gatherClubs();
        gatherCourses();
        recalc();
       
        $('#form').submit();
    });

    $('#form').validate({
        rules: {
            family: { familyTest : true },
            homephone: {
                require_from_group: [1, '.phone']
            },
            cellphone: {
                require_from_group: [1, '.phone']
            }
        },
        messages: {
            family: "The number of family members does not match the number being paid for",
            homephone: "Home Phone or Cell Phone is required",
            cellphone: "Home Phone or Cell Phone is required"
        },
        submitHandler: function(form) {
            form.submit();
        },
        invalidHandler: function(event, validator){
            let errors = validator.numberOfInvalids();
            if (errors){
                $('#errors').text("Something isn't right");
            }
        },
        errorClass: "error"
    });

    let urlParams = new URLSearchParams(window.location.search);
    if(urlParams.has('type')){
        switch (urlParams.get('type')){
            case "renew": 
                $('#renewMember').prop("checked", true);
                break;
            case "new":
                $('#newMember').prop("checked", true);
                break;
            default:
                $('#newMember').prop("checked", true);
        }
    } else {
        $('#newMember').prop("checked", true);
    }
    applicationTypeChange();
});