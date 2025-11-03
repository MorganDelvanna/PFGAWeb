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
        <link rel="icon" href="../images/pfgalogo.ico" type="image/icon type">
        <script src="https://js.stripe.com/v3/"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/additional-methods.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.8/dist/jquery.inputmask.min.js"></script>
        <script src="../js/member.js?v=1"></script>
    </head>

    <body>
    <div class="row">
        <div class="col-12 center">
            <img alt="Peterborough Fish & Game Association" src="../images/header.gif" />
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div id="menu">

            </div>
        </div>
    </div>
        <h1>Membership Application</h1>
        <h2><span id="memberYear">October 1st, 2025 - September 30th, 2026</span></h2>
        <form id="form" method="post" action="checkout.php">
            <input id="csrfToken" name="token" type="hidden" value="<?php echo $token ?>">
            <p>All required fields must be complete. Applications that are illegible, incomplete or incorrect WILL NOT BE ACCEPTED.<br />
                The membership application and payment form must be submitted with appropriate fees for presentation to the Board of Directors. Applications without payment WILL NOT BE ACCEPTED.</p>
            <p>As part of the new member application process please submit a photo for each family member to be used for ID. Email your photo(s) to membership@pfga.ca. The photo does not need to be professional, it can be taken on your phone. It should look like a passport photo. Please stand in front of a plain, preferably light coloured, background and include your head and shoulders. You can smile or not, whichever you prefer. Your face needs to be clearly seen. Thank you. </p>
            <br />
            <div class="row">
                <div class="col-12"><strong>Application Type: </strong>(Please select one of the following options)</div>
            </div>
            <div class="row mt-1">
                <div class="col-4 col-md-2">
                    <input type="radio" id="newMember" name="applicationType" value="new" checked> <label for="newMember">New Membership (Full Year)</label> 
                </div>
                <div class="col-4 col-md-2" id="halfColumn">
                   <input type="radio" id="halfMember" name="applicationType" value="half"> <label for="halfMember">New Membership (Half Year)*</label>
                </div>
                <div class="col-4 col-md-2">
                    <input type="radio" id="renewMember" name="applicationType" value="renew"> <label for="renewMember">Membership Renewal</label> 
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                   <span id="halfSpan">Note: Half-Year Applications are for NEW MEMBERS ONLY who join April 1st - Sept 30th.</span>
                </div>
            </div>
  
            <hr />

            <div class="row">
                <div class="col-12">
                    <span><strong>Personal Information:</strong> (Please Complete all <span class="required">REQUIRED</span> fields)</span>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-1">
                    <label for="firstname" class="title required">First Name</label><br />
                    <input id="firstname" type="text" class="new" name="firstname" required>
                </div>
                <div class="col-12 col-md-1">
                    <label for="lastname" class="title required">Last Name</label><br />
                    <input id="lastname" type="text" class="new" name="lastname" required>
                </div>
                <div class="col-12 col-md-1">
                    <label for="alias" class="title">Preferred Name</label><br />
                    <input id="alias" type="text" name="alias">
                </div>
                <div class="col-12 col-md-2">
                    <label for="dob" class="title required new" >Date of Birth</label><br/>
                    <input id="dob" type="date" class="new" name="dob" required>
                </div>
                <div id="cardCell" class="col-12 col-md-1 hidden">
                    <label id="cardLabel" for="card" class="title">PFGA Card #</label><br/>
                    <input id="card" type="text" name="card">
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-3">
                    <label for="address" class="title required new">Home Address</label><br />
                    <input id="address" type="text" class="new" name="address" required>
                </div>
                <div class="col-12 col-md-1" >
                    <label for="city" class="title required new">City</label><br />
                    <input id="city" type="text" class="new" name="city" required>
                </div>
                <div class="col-12 col-md-1">
                    <label for="province" class="title required new">Province</label><br />
                    <input id="province" type="text" class="new" name="province" required>
                </div>
                <div class="col-12 col-md-1">
                    <label for="postal" class="title required new">Postal Code</label><br />
                    <input id="postal" type="text" class="new" name="postal" required>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-1">
                    <label for="homephone" class="title">Home Phone *</label><br/>
                    <input id="homephone" type="text" class="new phone" name="homephone" >
                </div>
                <div class="col-12 col-md-1">
                    <label for="cellphone" class="title">Cell Phone *</label><br />
                    <input id="cellphone" type="text" class="new phone" name="cellphone" >
                </div>
                <div class="col-12 col-md-3">
                    <label for="email" class="title required new">E-Mail Address</label>
                    <input type="text" id="email" class="new" name="email" required>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-2">
                    <div class="row">
                        <div class="col-4 centered">
                            <label for="pal" class="title required">PAL</label><br />
                            <input type="radio" id="pal" name="palType" value="pal" required checked>
                        </div>
                        <div class="col-4 centered">
                            <label for="rpal" class="title required">RPAL</label><br />
                            <input type="radio" id="rpal" name="palType" value="rpal">
                        </div>
                        <div class="col-4 centered">
                            <label for="noPal" class="title required">None</label><br />
                            <input type="radio" id="noPal" name="palType" value="noPal">
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <label for="palDate" class="title">Approx. Date of PAL Course (MM-YY)</label>
                    <input type="text" id="palDate" name="palDate">
                </div>                    
                <div class="col-12 col-md-1">
                    <label for="PALNum" class="title required pal">PAL/RPAL #</label>
                    <input type="text" id="PALNum" name="palNum" required>
                </div>
                <div class="col-12 col-md-1">
                    <label for="palExpiry" class="title required pal">Pal Expiry Date</label>
                    <input type="date" id="palExpiry" name="palExpiry" required>
                </div>
            </div>
            <hr />
            <div class="row mt-2 newOnly">
                <div class="col-12">
                    <span><strong>Please select the section(s) you would like to join:</strong> (New Members Only)</span>
                </div>
                <div class="col-12 col-md-1" style="align-content: center;">
                    <input type="checkbox" id="archery" name="archery">
                    <label for="archery">Archery</label>
                </div>
                <div class="col-12 col-md-1" style="align-content: center;">
                    <input type="checkbox" id="rifle" name="rifle">
                    <label for="rifle">Rifle</label>
                </div>
                <div class="col-12 col-md-1" style="align-content: center;">
                    <input type="checkbox" id="smallbore" name="smallbore">
                    <label for="smallbore">Smallbore</label>
                </div>
                <div class="col-12 col-md-1" style="align-content: center;">
                    <input type="checkbox" id="handgun" name="handgun">
                    <label for="handgun">Handgun</label>
                </div>
                <div class="col-12 col-md-1" style="align-content: center;">
                    <input type="checkbox" id="action" name="action">
                    <label for="action">Action*</label>
                </div>
                <div class="col-12">
                    <span><strong>Note:</strong> In order to join Action Pistol you must first join Handgun, or be a current active member of an action shooting organization such as IPSC, IDPA, or ICORE. Please include this in the training & certifications section.</span>
                </div>
            </div>
            <hr class="newOnly" />
            <div class="row mt-2">
                <div class="col-12 col-md-8">
                    <strong>Additional Family Members:</strong> (Please ensure you included the appropriate number of family members in the Membership Fees section above)<br />
                            <button type="button" id="addFamily">Add Family</button>
                </div>
            </div>
            <div class="row familyRow">
                <div class="d-none d-sm-block col-md-2"><label class="centered required">First Name</label></div>
                <div class="d-none d-sm-block col-md-2"><label class="centered required">Last Name</label></div>
                <div class="d-none d-sm-block col-md-1"><label class="centered">PAL#</label></div>
                <div class="d-none d-sm-block col-md-1"><label class="centered">PAL Expiry</label></div>
                <div class="d-none d-sm-block col-md-1"><label class="centered required">Date of Birth</label></div>
            </div>                   

            <script id="familyTemplate" type="text/x-custom-template">
                <div class="row mt-2 familyRow">
                    <div class="col-6 d-md-none"><label class="required">First Name: </label></div><div class="col-6 col-md-2"><input type="text" class="famName" required aria-label="First Name"></div>
                    <div class="col-6 d-md-none"><label class="required">Last Name: </label></div><div class="col-6 col-md-2"><input type="text" class="famLast" required aria-label="Last Name"></div>
                    <div class="col-6 d-md-none"><label class="required">PAL#: </label></div><div class="col-6 col-md-1"><input type="text" class="famPAL" aria-label="PAL #"></div>
                    <div class="col-6 d-md-none"><label class="required">Pal Expiry: </label></div><div class="col-6 col-md-1"><input type="date" class="famExpiry" aria-label="Pal Expiry"></div>
                    <div class="col-6 d-md-none"><label class="required">Date of Birth: </label></div><div class="col-6 col-md-1"><input type="date" class="famDOB" required aria-label="Date of Birth"></div>
                    <div class="col-12 col-md-1"><button type="button" class="btnDeleteFam">delete</button><input type="hidden" name="familyMembers[]"></div>
                </div>
            </script>
                
            <hr />
            <div class="row mt-2 newOnly">
                <div class="col-12 col-md-7">
                    <strong>Other Club Affiliations Past or Present:</strong> (New Members Only - Optional)<br />
                    <button id="addClub" type="button">Add Club</button>
                </div>                        
            </div>
            <div class="row clubRow newOnly">
                <div class="d-none d-sm-block col-md-2 centered">Club Name</div>
                <div class="d-none d-sm-block col-md-2 centered">City, Province</div>
                <div class="d-none d-sm-block col-md-1 centered">From (MM/YY)</div>
                <div class="d-none d-sm-block col-md-1 centered">To (MM/YY)</div>
            </div>
            <script id="clubTemplate" type="text/x-custom-template">
                <div class="row clubRow">
                    <div class="col-6 d-md-none">Club Name: </div><div class="col-6 col-md-2"><input type="text" class="otherClub"></div>
                    <div class="col-6 d-md-none">City, Province: </div><div class="col-6 col-md-2"><input type="text" class="otherCity"></div>
                    <div class="col-6 d-md-none">From (MM/YY): </div><div class="col-6 col-md-1"><input type="text" class="otherFrom" placeholder="MM/YY"></div>
                    <div class="col-6 d-md-none">To (MM/YY): </div><div class="col-6 col-md-1"><input type="text" class="otherTo" placeholder="MM/YY"></div>
                    <div class="col-12 col-md-1"><button type="button" class="btnDeleteClub">delete</button>      
                        <input type="hidden" name="otherClubs[]">
                    </div>
                </div>
            </script>
                
            <hr class="newOnly" />
            <div class="row mt-2 newOnly">
                <div class="col-12 col-md-8">
                    <strong>Firearms/Archery Training or Certifications:</strong> (New Members Only - Optional)<br />
                    <button type="button" id="addCourse">Add Training/Certification</button>
                </div>
            </div>
            <div class="row courseRow newOnly">
                <div class="d-none d-sm-block col-md-2 centered">Description</div>
                <div class="d-none d-sm-block col-md-2 centered">Instructor/Trainer</div>
                <div class="d-none d-sm-block col-md-2 centered">Location</div>
                <div class="d-none d-sm-block col-md-1 centered">Date (MM/YY)</div>
            </div>                   
            <script id="courseTemplate" type="text/x-custom-template">
                <div class="row courseRow">
                    <div class="col-6 d-md-none">Description: </div><div class="col-6 col-md-2"><input type="text" class="courseDesc"></div>
                    <div class="col-6 d-md-none">Instructor/Trainer: </div><div class="col-6 col-md-2"><input type="text" class="courseTrainer"></div>
                    <div class="col-6 d-md-none">Location: </div><div class="col-6 col-md-2"><input type="text" class="courseLocation"></div>
                    <div class="col-6 d-md-none">Date (MM/YY): </div><div class="col-6 col-md-1"><input type="text" class="courseDate" placeholder="MM/YY"></div>
                    <div class="col-12 col-md-1"><button type="button" class="btnDeleteCourse">delete</button>
                        <input type="hidden" name="courses[]">
                    </div>
                </div>
            </script>                        
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
            <div class="row mt-2">
                <div class="col-8 col-md-1">
                    <span id="errors" class="error"></span>
                    <button type="button" id="btnSubmit">Submit</button>
                </div>
            </div>          
        </form>
        <input type="hidden" id="pageRef" value="#l_membership" />
        <script src="../js/bootstrap.js" type="text/javascript"></script>
        <script src="../js/common.js" type="text/javascript"></script>
    </body>
</html>