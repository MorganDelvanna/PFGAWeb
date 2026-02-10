<?php
    require_once 'shared.php';
    
    if (empty($_SESSION['token'])) {
        $_SESSION['token'] = bin2hex(random_bytes(32));
    }
    $token = $_SESSION['token'];
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <title>Peterborough Fish & Game Association</title>

        <link rel="stylesheet" href="../css/bootstrap.min.css" />
        <link rel="stylesheet" href="../css/bootstrap-grid.min.css">
        <link rel="stylesheet" href="../css/pfga.css">
        <link rel="stylesheet" href="../css/style.css">    
        <link rel="stylesheet" href="../css/menu.css" />     
        <link rel="icon" href="../images/pfgalogo.ico" type="image/icon type">
        <script src="https://js.stripe.com/v3/"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/additional-methods.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.8/dist/jquery.inputmask.min.js"></script>
    </head>

    <body>
        <div class="container">
            <div class="row">
                <div class="col-12 center">
                    <img alt="Peterborough Fish & Game Association" src="../images/header.gif" />
                </div>
            </div>
            <div class="row">
                <div class="col-12 app">
                    <menu-control></menu-control>
                </div>
            </div>
            <h1>Membership Application</h1>
            <h2><span id="memberYear">October 1st, 2025 - September 30th, 2026</span></h2>
            <form id="form" method="post" action="">
                <input id="csrfToken" name="token" type="hidden" value="<?php echo $token ?>">
                <div id="memberForm"></div>
                <button type="button" id="btnAddMember">Add Member</button>
                <div id="memberlist">
                    <div class="row memberRow">
                        <div class="col-8">Member</div>
                        <div class="col-4">Cost</div>
                    </div>
                </div>
                <div id="paymentform">
                    <hr class="newOnly" />
                    <div class="row mt-2">
                        <div class="col-12">
                            <strong>Membership Fees:</strong> (Please select the appropriate membership options and add the appropriate fees in the membership dues column). Fees are not refundable.
                        </div>                                           
                    </div>
                    <div class="row">
                        <div class="col-6"><strong>Membership<span class="d-none d-sm-block"> Options</span></strong></div>
                        <div class="d-none d-sm-block col-md-2"><strong>Full Year (Oct 1 - Sept 30)</strong></div>
                        <div class="d-none d-sm-block col-md-2"><strong>Half Year (April 1 - Sept 30)</strong></div>
                        <div class="col-6 col-md-2"><strong><span class="d-none d-sm-block">Membership </span>Dues</strong></div>
                    </div>
                    <div class="row">
                        <div class="d-none d-sm-block col-md-6">New Member Initiation Fee (New Members Only)</div>
                        <div class="col-6 d-md-none">Initiation Fee (<span class="d-md-none initiaionFee">$75</span>)</div>
                        <div class="d-none d-sm-block col-md-2"><span class="initiaionFee">$75</span></div>
                        <div class="d-none d-sm-block col-md-2"><span class="initiaionFee">$75</span></div>
                        <div class="col-6 col-md-2"><span id="initiationFee"></span></div>
                    </div>
                    <div class="row">
                        <div class="d-none d-sm-block col-md-6">General Membership Fee</div>
                        <div class="col-6 d-md-none">General Fee</div>
                        <div class="d-none d-sm-block col-md-2"><span class="generalFee"></span></div>
                        <div class="d-none d-sm-block col-md-2"><span class="generalHalf"></span></div>
                        <div class="col-6 col-md-2"><input id="generalBtn" type="radio" title="General Membership" name="membershipFee" value="general" checked>&nbsp;<span id="generalFee"></span></div>
                    </div>
                    <div class="row">
                        <div class="d-none d-sm-block col-md-6">Senior Membership Fee (65+)</div>
                        <div class="col-6 d-md-none">Senior Fee (65+)</div>
                        <div class="d-none d-sm-block col-md-2"><span class="seniorFee"></span></div>
                        <div class="d-none d-sm-block col-md-2"><span class="seniorHalf"></span></div>
                        <div class="col-6 col-md-2"><input type="radio" title="Senior Membership" name="membershipFee" value="senior">&nbsp;<span id="seniorFee"></span></div>
                    </div>
                    <div class="row">
                        <div class="d-none d-sm-block col-md-6">Junior Membership Fee (12-18)</div>
                        <div class="col-6 d-md-none">Junior Fee (12-18)</div>
                        <div class="d-none d-sm-block col-md-2"><span class="juniorFee"></span></div>
                        <div class="d-none d-sm-block col-md-2"><span class="juniorHalf"></span></div>
                        <div class="col-6 col-md-2"><input type="radio" title="Junior Membership" name="membershipFee" value="junior">&nbsp;<span id="juniorFee"></span></div>
                    </div>
                    <div class="row">
                        <div class="d-none d-sm-block col-md-6">Summer Archery (Outdoor Archery Only - April 1 to Sept 30)</div>
                        <div class="col-6 d-md-none">Summer Archery</div>
                        <div class="d-none d-sm-block col-md-2">N/A</div>
                        <div class="d-none d-sm-block col-md-2"><span class="generalHalf"></span></div>
                        <div class="col-6 col-md-2"><input id="archeryBtn" type="radio" title="Archery Membership" name="membershipFee" value="archery" class="hidden">&nbsp;<span Id="archeryFee"></span></div>
                    </div>
                    <div class="row">
                        <div class="d-none d-sm-block col-md-6">Additional Family Members<</div>
                        <div class="col-6 d-md-none">Extra Family</div>
                        <div class="d-none d-sm-block col-md-2"><span class="familyFee"></span></div>
                        <div class="d-none d-sm-block col-md-2"><span class="familyFee"></span></div>
                        <div class="col-2 col-md-2"><input type="number" id="family" name="family" value="0"></div>
                    </div>
                    <div class="row">
                        <div class="d-none d-sm-block col-md-6">Extra Swipe Cards<br />
                                                <span><strong>Note:</strong> Extra swipe cards are for adults who will require their own swipe card to access the club</span></div>
                        <div class="col-6 d-md-none">Extra Swipe Cards</div>
                        <div class="d-none d-sm-block col-md-2"><span class="extraFee"></span></div>
                        <div class="d-none d-sm-block col-md-2"><span class="extraFee"></span></div>
                        <div class="col-2 col-md-2"><input type="number" id="extra" name="extra" value="0"></div>                
                    </div>
                    <div class="row">
                        <div class="col-6 col-md-10 align-content-end"><strong>Amount Due:</strong></div>
                        <div class="col-6 col-md-2">&nbsp;$<span id="total"></span></div>
                    </div>         
                    <div class="row mt-2 terms">
                        <div class="col-12">
                            <input type="checkbox" id="terms" name="terms" required>
                            <label for="terms">I agree that I have read the all the instructions and I hereby declare that the information provided is true and correct</label>
                        </div>
                    </div>    
                </div>
                <div class="row mt-2">
                    <div class="col-8 col-md-1">
                        <span id="errors" class="error"></span>
                        <button type="button" id="btnSubmit">Submit</button>
                    </div>
                </div>          
            </form>
        </div>
        <script id="memberTemplate" type="text/x-custom-template">
            <div class="row memberRow">
                <div class="col-8"><span class="memberName"></span></div>
                <div class="col-3"><span class="memberCost"></span></div>
                <div class="col-12 col-md-1"><button type="button" class="btnDeleteMember">Delete</button>
                <input type="hidden" class="memberJson"></input>
                <input type="hidden" class="fee"></input>
                <input type="hidden" class="initiation"></input>
                <input type="hidden" class="family"></input>
                <input type="hidden" class="cards"></input>
            </div>
        </script>
        <script src="../js/bootstrap.js" type="text/javascript"></script>
        <!--<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
        <script src="../js/vue.js"></script>-->
        <script type="text/javascript">
            const collectDisciplines = function () {
                const disciplines = [];
                if ($('#archery').prop("checked")) {
                    disciplines.push('archery');
                }
                if ($('#rifle').prop("checked")) {
                    disciplines.push('rifle');
                }
                if ($('#smallbore').prop("checked")) {
                    disciplines.push('smallbore');
                }
                if ($('#handgun').prop("checked")) {
                    disciplines.push('handgun');
                }
                if ($('#action').prop("checked")) {
                    disciplines.push('action');
                }
                return disciplines.toString()
            }

            const collectMember = function () {
                let disciplines = collectDisciplines();
                let member = {
                    firstName: $('#firstname').val(),
                    lastName: $('#lastname').val(),
                    alias: $('#alias').val(),
                    dob: $('#dob').val(),
                    card: $('#card').val(),
                    address: $('#address').val(),
                    city: $('#city').val(),
                    province: $('#province').val(),
                    postal: $('#postal').val(),
                    homephone: $('#homephone').val(),
                    cellphone: $('#cellphone').val(),
                    email: $('#email').val(),
                    palType: $('[name="palType"]:checked').val(),
                    palDate: $('#palDate').val(),
                    PALNum: $('#PALNum').val(),
                    palExpiry: $('#palExpiry').val(),
                    disciplines: disciplines
                };
                return member;
            };

            const addMember = function () {
                let template = $('#memberTemplate').html();
                $('.memberRow:last').after(template);

                let memberRow = $('.memberRow:last');
                let member = collectMember();
                console.log(member);

                memberRow.find('.memberName').text(`${member.firstName} ${member.lastName}`);
                memberRow.find('.memberCost').text('$350');
                memberRow.find('.memberJson').val(member.toString());

                $('.btnDeleteFam').on('click', function(){
                    $(this).closest(".memberRow").remove();
                               
                });       

            };

            $(function () {
                $('#memberForm').load("partials/memberform.htm")

                $('#btnAddMember').on('click', addMember);
            });
        </script>
    </body>
</html>