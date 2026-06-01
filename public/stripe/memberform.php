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
        <script type="text/javascript">
            <?php include 'fees.php'; ?>
        </script>
        <script src="../js/member.js?v=1"></script>
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
        <form id="form" method="post" action="checkout.php">
            <input id="csrfToken" name="token" type="hidden" value="<?php echo $token ?>">
            <input type="hidden" id="members_json" name="members_json"  value="">
            <p>All required fields must be complete. Applications that are illegible, incomplete or incorrect WILL NOT BE ACCEPTED.<br />
                The membership application and payment form must be submitted with appropriate fees for presentation to the Board of Directors. Applications without payment WILL NOT BE ACCEPTED.</p>
            <p>As part of the new member application process please submit a photo for each family member to be used for ID. Email your photo(s) to membership@pfga.ca. The photo does not need to be professional, it can be taken on your phone. It should look like a passport photo. Please stand in front of a plain, preferably light coloured, background and include your head and shoulders. You can smile or not, whichever you prefer. Your face needs to be clearly seen. Thank you. </p>
            <br />
            <div class="row">
                <div class="col-12"><strong>Application Type: </strong>(Please select one of the following options)</div>
            </div>
            <div class="row mt-1">
                <div class="col-12 col-md-4">
                    <input type="radio" id="newMember" name="applicationType" value="new" checked> <label for="newMember">New Membership (Full Year)</label> 
                </div>
                <div class="col-12 col-md-4" id="halfColumn">
                   <input type="radio" id="halfMember" name="applicationType" value="half"> <label for="halfMember">New Membership (Half Year)*<br />April to September</label>
                </div>
                <div class="col-12 col-md-4">
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
                <div class="col-12 col-md-2">
                    <label for="firstname" class="title required">First Name</label><br />
                    <input id="firstname" type="text" class="new" name="firstname" required>
                </div>
                <div class="col-12 col-md-2">
                    <label for="lastname" class="title required">Last Name</label><br />
                    <input id="lastname" type="text" class="new" name="lastname" required>
                </div>
                <div class="col-12 col-md-2">
                    <label for="alias" class="title">Preferred Name</label><br />
                    <input id="alias" type="text" name="alias">
                </div>
                <div class="col-12 col-md-2">
                    <label for="dob" class="title required new" >Date of Birth</label><br/>
                    <input id="dob" type="date" class="new" name="dob" required>
                </div>
                <div id="cardCell" class="col-12 col-md-3 hidden">
                    <label id="cardLabel" for="pfgaNumber" class="title">PFGA Card #</label><br/>
                    <input id="pfgaNumber" type="text" name="pfgaNumber">
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-3">
                    <label for="address" class="title required new">Home Address</label><br />
                    <input id="address" type="text" class="new" name="address" required>
                </div>
                <div class="col-12 col-md-2" >
                    <label for="city" class="title required new">City</label><br />
                    <input id="city" type="text" class="new" name="city" required>
                </div>
                <div class="col-12 col-md-2">
                    <label for="province" class="title required new">Province</label><br />
                    <input id="province" type="text" class="new" name="province" required>
                </div>
                <div class="col-12 col-md-2">
                    <label for="postal" class="title required new">Postal Code</label><br />
                    <input id="postal" type="text" class="new" name="postal" required>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-2">
                    <label for="homephone" class="title">Home Phone *</label><br/>
                    <input id="homephone" type="text" class="new phone" name="homephone" >
                </div>
                <div class="col-12 col-md-2">
                    <label for="cellphone" class="title">Cell Phone *</label><br />
                    <input id="cellphone" type="text" class="new phone" name="cellphone" >
                </div>
                <div class="col-12 col-md-4">
                    <label for="email" class="title required new">E-Mail Address</label>
                    <input type="text" id="email" class="new" name="email" required>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-4">
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
                <div class="col-12 col-md-2">
                    <label for="PALNum" class="title required pal">PAL/RPAL #</label>
                    <input type="text" id="PALNum" name="palNum" required>
                </div>
                <div class="col-12 col-md-2">
                    <label for="palExpiry" class="title required pal">Pal Expiry Date</label>
                    <input type="date" id="palExpiry" name="palExpiry" required>
                </div>
            </div>
            <hr />
            <div class="row mt-2 newOnly">
                <div class="col-12">
                    <span><strong>Please select the section(s) you would like to join:</strong> (New Members Only)</span>
                </div>
                <div class="col-12 col-md-2" style="align-content: center;">
                    <input type="checkbox" id="archery" name="archery">
                    <label class="ml-1" for="archery">Archery</label>
                </div>
                <div class="col-12 col-md-2" style="align-content: center;">
                    <input type="checkbox" id="rifle" name="rifle">
                    <label class="ml-1" for="rifle">Rifle</label>
                </div>
                <div class="col-12 col-md-2" style="align-content: center;">
                    <input type="checkbox" id="smallbore" name="smallbore">
                    <label class="ml-1" for="smallbore">Smallbore</label>
                </div>
                <div class="col-12 col-md-2" style="align-content: center;">
                    <input type="checkbox" id="handgun" name="handgun">
                    <label class="ml-1" for="handgun">Handgun</label>
                </div>
                <div class="col-12 col-md-2" style="align-content: center;">
                    <input type="checkbox" id="action" name="action">
                    <label class="ml-1" for="action">Action*</label>
                </div>
                <div class="col-12">
                    <span><strong>Note:</strong> In order to join Action Pistol you must first join Handgun, or be a current active member of an action shooting organization such as IPSC, IDPA, or ICORE. Please include this in the training & certifications section.</span>
                </div>
            </div>
            <hr class="newOnly" />
            <div class="row mt-2">
                <div class="col-12 col-md-8">
                    <strong>Additional Family Members:</strong> (Please ensure you included the appropriate number of family members in the Membership Fees section below)<br />
                    <button type="button" id="addFamilyVue">Add Family</button>
                </div>
            </div>

            <div id="familyApp">
                <div class="row familyRow">
                    <div class="d-none d-sm-block col-md-2"><label class="centered required">First Name</label></div>
                    <div class="d-none d-sm-block col-md-3"><label class="centered required">Last Name</label></div>
                    <div class="d-none d-sm-block col-md-2"><label class="centered">PAL#</label></div>
                    <div class="d-none d-sm-block col-md-2"><label class="centered">PAL Expiry</label></div>
                    <div class="d-none d-sm-block col-md-2"><label class="centered required">Date of Birth</label></div>
                </div>

                <div class="row mt-2 familyRow" v-for="(m, idx) in members" :key="idx">
                    <div class="col-6 d-md-none"><label class="required">First Name: </label></div>
                    <div class="col-6 col-md-2"><input type="text" class="famName" v-model="m.firstname" required aria-label="First Name"></div>
                    <div class="col-6 d-md-none"><label class="required">Last Name: </label></div>
                    <div class="col-6 col-md-3"><input type="text" class="famLast" v-model="m.lastname" required aria-label="Last Name"></div>
                    <div class="col-6 d-md-none"><label class="required">PAL#: </label></div>
                    <div class="col-6 col-md-2"><input type="text" class="famPAL" v-model="m.pal" aria-label="PAL #"></div>
                    <div class="col-6 d-md-none"><label class="required">Pal Expiry: </label></div>
                    <div class="col-6 col-md-2"><input type="date" class="famExpiry" v-model="m.palExpiry" aria-label="Pal Expiry"></div>
                    <div class="col-6 d-md-none"><label class="required">Date of Birth: </label></div>
                    <div class="col-6 col-md-2"><input type="date" class="famDOB" v-model="m.dob" required aria-label="Date of Birth"></div>
                    <div class="col-12 col-md-1"><button type="button" class="btnDeleteFam" @click="removeFamily(idx)">delete</button>
                        <input type="hidden" :name="'familyMembers[]'" :value="formatMember(m)">
                    </div>
                </div>
            </div>
                
            <hr />
            <div id="clubsApp" class="newOnly">
                <div class="row mt-2">
                    <div class="col-12 col-md-7">
                        <strong>Other Club Affiliations Past or Present:</strong> (New Members Only - Optional)<br />
                        <button id="addClubVue" type="button">Add Club</button>
                    </div>
                </div>
                <div class="row clubRow">
                    <div class="d-none d-sm-block col-md-3 centered">Club Name</div>
                    <div class="d-none d-sm-block col-md-3 centered">City, Province</div>
                    <div class="d-none d-sm-block col-md-2 centered">From (MM/YY)</div>
                    <div class="d-none d-sm-block col-md-2 centered">To (MM/YY)</div>
                </div>

                <div class="row clubRow" v-for="(c, idx) in clubs" :key="idx">
                    <div class="col-6 d-md-none">Club Name: </div><div class="col-6 col-md-3"><input type="text" v-model="c.name" class="otherClub"></div>
                    <div class="col-6 d-md-none">City, Province: </div><div class="col-6 col-md-3"><input type="text" v-model="c.city" class="otherCity"></div>
                    <div class="col-6 d-md-none">From (MM/YY): </div><div class="col-6 col-md-2"><input type="text" v-model="c.from" class="otherFrom" placeholder="MM/YY"></div>
                    <div class="col-6 d-md-none">To (MM/YY): </div><div class="col-6 col-md-2"><input type="text" v-model="c.to" class="otherTo" placeholder="MM/YY"></div>
                    <div class="col-12 col-md-1"><button type="button" class="btnDeleteClub" @click="removeClub(idx)">delete</button>
                        <input type="hidden" :name="'otherClubs[]'" :value="formatClub(c)">
                    </div>
                </div>
            </div>
                
            <hr class="newOnly" />
            <div id="coursesApp" class="newOnly">
                <div class="row mt-2">
                    <div class="col-12 col-md-8">
                        <strong>Firearms/Archery Training or Certifications:</strong> (New Members Only - Optional)<br />
                        <button type="button" id="addCourseVue">Add Training/Certification</button>
                    </div>
                </div>
                <div class="row courseRow">
                    <div class="d-none d-sm-block col-md-3 centered">Description</div>
                    <div class="d-none d-sm-block col-md-3 centered">Instructor/Trainer</div>
                    <div class="d-none d-sm-block col-md-3 centered">Location</div>
                    <div class="d-none d-sm-block col-md-2 centered">Date (MM/YY)</div>
                </div>

                <div class="row courseRow" v-for="(c, idx) in courses" :key="idx">
                    <div class="col-6 d-md-none">Description: </div><div class="col-6 col-md-3"><input type="text" v-model="c.desc" class="courseDesc"></div>
                    <div class="col-6 d-md-none">Instructor/Trainer: </div><div class="col-6 col-md-3"><input type="text" v-model="c.trainer" class="courseTrainer"></div>
                    <div class="col-6 d-md-none">Location: </div><div class="col-6 col-md-3"><input type="text" v-model="c.location" class="courseLocation"></div>
                    <div class="col-6 d-md-none">Date (MM/YY): </div><div class="col-6 col-md-2"><input type="text" v-model="c.date" class="courseDate" placeholder="MM/YY"></div>
                    <div class="col-12 col-md-1"><button type="button" class="btnDeleteCourse" @click="removeCourse(idx)">delete</button>
                        <input type="hidden" :name="'courses[]'" :value="formatCourse(c)">
                    </div>
                </div>
            </div>
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
            <div class="row mt-2">
                <div class="col-6 col-md-10 align-content-end"><strong>Grand Total (All Applicants):</strong></div>
                <div class="col-6 col-md-2"><strong>$<span id="grandTotal">0</span></strong></div>
            </div>         
            <div class="row mt-2 terms">
                <div class="col-12">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label class="ml-1" for="terms">I agree that I have read the all the instructions and I hereby declare that the information provided is true and correct</label>
                </div>
            </div>              
            <div class="row mt-2">
                <div class="col-8 col-md-4">
                    <span id="errors" class="error"></span>
                    <button type="button" id="addApplicant">Add Another Applicant</button>                    
                </div>
            </div>          
        </form>
        <hr />
        <div class="row mt-2">
            <div class="col-12">
                <button type="button" id="btnSubmit">Submit</button>
                <span id="applicantCount">0 applicants added</span>
                <div id="applicantList"></div>
            </div>
        </div>
    </div>
    
        <script src="../js/bootstrap.js" type="text/javascript"></script>
        <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
        <script src="../js/vue.js"></script>
        <script>
            (function(){
                const { createApp } = Vue;

                // Form-level Vue app to manage conditional UI, clubs and courses and validation
                window.formVm = createApp({
                    data() {
                        return {
                            applicationType: document.querySelector('input[name="applicationType"]:checked')?.value || 'new',
                            palType: document.querySelector('input[name="palType"]:checked')?.value || 'pal',
                            membershipFee: document.querySelector('input[name="membershipFee"]:checked')?.value || 'general',
                            clubs: [],
                            courses: [],
                            members: []
                        };
                    },
                    methods: {
                        addClub() { this.clubs.push({ name: '', city: '', from: '', to: '' }); },
                        removeClub(i){ this.clubs.splice(i,1); },
                        formatClub(c){ return `${c.name || ''} ${c.city || ''} ${c.from || ''} - ${c.to || ''}`; },
                        addCourse(){ this.courses.push({ desc: '', trainer: '', location: '', date: '' }); },
                        removeCourse(i){ this.courses.splice(i,1); },
                        formatCourse(c){ return `${c.desc || ''} ${c.trainer || ''} ${c.location || ''} ${c.date || ''}`; },
                        addFamily() { this.members.push({ firstname: '', lastname: '', pal: '', palExpiry: '', dob: '' }); this.updateCount(); },
                        removeFamily(idx) { this.members.splice(idx,1); this.updateCount(); },
                        formatMember(m) { return `${m.firstname || ''} ${m.lastname || ''} DOB: ${m.dob || ''} PAL: ${m.pal || ''} ${m.palExpiry || ''}`; },
                        updateCount() { const el = document.getElementById('family'); if (el) el.value = this.members.length; if (typeof recalc === 'function') recalc(); },
                        // simple validation that complements jQuery Validate
                        validate() {
                            let ok = true;
                            // Don't validate if we're submitting multiple
                            const applicantCount = parseInt(document.querySelectorAll('#applicantList li').length || '0');
                            if (applicantCount > 0) return true;

                            // applicationType-specific required fields (class new)                            
                            document.querySelectorAll('input[required]').forEach(i=>{
                                if (!i.value || i.value.trim()==='') { 
                                    i.setAttribute('aria-invalid','true'); ok=false; 
                                    i.classList.add('failed');
                                }
                                else {
                                    i.removeAttribute('aria-invalid');
                                    i.classList.remove('failed');}
                            });
                            
                            // pal rules
                            if (this.palType === 'pal' || this.palType === 'rpal') {
                                const palNum = document.getElementById('PALNum');
                                const palExpiry = document.getElementById('palExpiry');
                                if (!palNum || !palNum.value.trim()) { 
                                    if(palNum) {
                                        palNum.setAttribute('aria-invalid','true'); ok=false; 
                                        palNum.classList.add('failed');
                                    }
                                }
                                else if (palNum) {
                                    palNum.removeAttribute('aria-invalid');
                                    palNum.classList.remove('failed');
                                }
                                if (!palExpiry || !palExpiry.value.trim()) { 
                                    if(palExpiry) {
                                        palExpiry.setAttribute('aria-invalid','true'); ok=false; 
                                        palExpiry.classList.add('failed');
                                    }
                                }
                                else if (palExpiry) {
                                    palExpiry.removeAttribute('aria-invalid');
                                    palExpiry.classList.remove('failed');
                                }
                            }
                            // phone requirement (home or cell)
                            const homePhone = document.getElementById('homephone')?.value || '';
                            const cellPhone = document.getElementById('cellphone')?.value || '';
                            const homePhoneInput = document.getElementById('homephone');
                            const cellPhoneInput = document.getElementById('cellphone');
                            if (homePhone.trim() === '' && cellPhone.trim() === '') {
                                homePhoneInput.setAttribute('aria-invalid','true'); 
                                cellPhoneInput.setAttribute('aria-invalid','true'); 
                                homePhoneInput.classList.add('failed');
                                cellPhoneInput.classList.add('failed');
                                ok=false; 
                            } else {
                                homePhoneInput.removeAttribute('aria-invalid'); homePhoneInput.classList.remove('failed'); 
                                cellPhoneInput.removeAttribute('aria-invalid'); cellPhoneInput.classList.remove('failed'); 
                            }
                            // family count vs members
                            const famCount = parseInt(document.getElementById('family')?.value || '0');
                            const familyMembers = (window.familyVm && Array.isArray(window.familyVm.members)) ? window.familyVm.members.length : Array.from(document.querySelectorAll('.famName')).filter(e => e.value && e.value.trim() !== '').length;
                            if (famCount !== familyMembers) {
                                const el = document.getElementById('family'); if (el) el.setAttribute('aria-invalid','true'); ok=false;
                            } else { const el = document.getElementById('family'); if (el) el.removeAttribute('aria-invalid'); }

                            // Terms checkbox
                            const termsChecked = document.getElementById('terms')?.checked;
                            const termsInput = document.getElementById('terms');
                            const termsDiv = document.querySelector('.terms');

                            if (!termsChecked) {    
                                termsInput.setAttribute('aria-invalid','true'); 
                                termsDiv.classList.add('failed');
                                ok=false; 
                            } else {
                                termsInput.removeAttribute('aria-invalid'); 
                                termsDiv.classList.remove('failed');
                            }

                            return ok;
                        }
                    },
                    watch: {
                        applicationType(v){
                            // reflect changes into DOM to keep existing scripts working
                            const node = document.querySelector(`input[name="applicationType"][value="${v}"]`);
                            if (node) node.checked = true;
                            if (typeof applicationTypeChange === 'function') applicationTypeChange();
                        },
                        palType(v){
                            const node = document.querySelector(`input[name="palType"][value="${v}"]`);
                            if (node) node.checked = true;
                            if (typeof $('input[name="palType"]').trigger === 'function') $('input[name="palType"]').trigger('change');
                        },
                        members: { handler(){ this.updateCount(); }, deep:true } 
                    }
                }).mount('#form');

                // wire clubs/courses add buttons
                const addClubBtn = document.getElementById('addClubVue'); if (addClubBtn && window.formVm) addClubBtn.addEventListener('click', ()=>window.formVm.addClub());
                const addCourseBtn = document.getElementById('addCourseVue'); if (addCourseBtn && window.formVm) addCourseBtn.addEventListener('click', ()=>window.formVm.addCourse());
                const addBtn = document.getElementById('addFamilyVue'); if (addBtn) addBtn.addEventListener('click', function(){ window.formVm.addFamily(); });
            })();
        </script>
    </body>
</html>