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
            <div class="row">
                <div class="col-12">
                    <h1>Additional Family & Cards</h1>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <form id="form" method="post" action="checkout.php">
                        <input id="csrfToken" name="token" type="hidden" value="<?php echo $token ?>">
                        <input id="applicationType" name="applicationType" type="hidden" value="update">
                        <input type="hidden" id="members_json" name="members_json" value="">
                        <input type="hidden" id="email" name="email" value="">
                        <!-- Add hidden checked radio controls so legacy member.js can read values via :checked selectors -->
                        <input type="radio" name="applicationType" value="update" checked style="display:none" aria-hidden="true">
                        <input type="radio" name="membershipFee" value="update" checked style="display:none" aria-hidden="true">
                        <div class="row">
                            <div class="col-12">
                                <p>All required fields must be complete. Applications that are illegible, incomplete or incorrect WILL NOT BE ACCEPTED and money sent will be considered a donation.</p>
                                <p>As part of the adding a family member process please submit a photo for each family member to be used for ID. Email your photo(s) to membership@pfga.ca.
                                    The photo does not need to be professional, it can be taken on your phone. It should look like a passport photo. Please stand in front of a plain, preferably light coloured,
                                    background and include your head and shoulders. You can smile or not, whichever you prefer. Your face needs to be clearly seen. Thank you. </p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <span><strong>Personal Information:</strong> (Please Complete all fields so we can add new family members or swipe cards to the correct account)</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <label for="firstname" class="form-label title required">First Name</label><br />
                                <input id="firstname" type="text" class="form-control new" name="firstname" data-no-auto-fill required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="lastname" class="form-label title required">Last Name</label><br />
                                <input id="lastname" type="text" class="form-control new" name="lastname" data-no-auto-fill required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label id="cardLabel" for="pfgaNumber" class="form-label title required">PFGA Card #</label><br/>
                                <input id="pfgaNumber" type="text" class="form-control" name="pfgaNumber" data-no-auto-fill required>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <strong>Additional Family Members:</strong> (Please ensure you included the appropriate number of family members in the Membership Fees section below)<br />
                                        <button type="button" id="addFamily">Add Family</button>
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
                                <div class="col-6 col-md-2"><input type="text" class="form-control famName" v-model="m.firstname" required aria-label="First Name"></div>
                                <div class="col-6 d-md-none"><label class="required">Last Name: </label></div>
                                <div class="col-6 col-md-3"><input type="text" class="form-control famLast" v-model="m.lastname" required aria-label="Last Name"></div>
                                <div class="col-6 d-md-none"><label class="required">PAL#: </label></div>
                                <div class="col-6 col-md-2"><input type="text" class="form-control famPAL" v-model="m.pal" aria-label="PAL #"></div>
                                <div class="col-6 d-md-none"><label class="required">Pal Expiry: </label></div>
                                <div class="col-6 col-md-2"><input type="date" class="form-control famExpiry" v-model="m.palExpiry" aria-label="Pal Expiry"></div>
                                <div class="col-6 d-md-none"><label class="required">Date of Birth: </label></div>
                                <div class="col-6 col-md-2"><input type="date" class="form-control famDOB" v-model="m.dob" required aria-label="Date of Birth"></div>
                                <div class="col-12 col-md-1"><button type="button" class="btnDeleteFam" @click="removeFamily(idx)">delete</button>
                                    <input type="hidden" :name="'familyMembers[]'" :value="formatMember(m)">
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
                            <div class="d-none d-sm-block col-md-6">Additional Family Members</div>
                            <div class="col-6 d-md-none">Extra Family</div>
                            <div class="d-none d-sm-block col-md-2"><span class="familyFee"></span></div>
                            <div class="col-2 col-md-2"><input type="number" id="family" name="family" value="0"></div>
                        </div>
                        <div class="row">
                            <div class="d-none d-sm-block col-md-6">Extra Swipe Cards</div>
                            <div class="col-6 d-md-none">Extra Swipe Cards</div>
                            <div class="d-none d-sm-block col-md-2"><span class="extraFee"></span></div>
                            <div class="col-2 col-md-2"><input type="number" id="extra" name="extra" value="0"></div>                
                        </div>
                        <div class="row">
                            <div class="col-12"><span><strong>Note:</strong> Extra swipe cards are for adults who will require their own swipe card to access the club</span></div>
                        </div>
                        <div class="row">
                            <div class="col-6 col-md-10 align-content-end"><strong>Amount Due:</strong></div>
                            <div class="col-6 col-md-2">&nbsp;$<span id="total"></span></div>
                        </div>         
                        <div class="row mt-2 terms">
                            <div class="col-12">
                                <input type="checkbox" id="terms" name="terms" required>
                                <label class="ml-1" for="terms">I agree that I have read the all the instructions and I hereby declare that the information provided is true and correct</label>
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
                            members: []
                        };
                    },
                    methods: {                        
                        addFamily() { this.members.push({ firstname: '', lastname: '', pal: '', palExpiry: '', dob: '' }); this.updateCount(); },
                        removeFamily(idx) { this.members.splice(idx,1); this.updateCount(); },
                        formatMember(m) { return `${m.firstname || ''} ${m.lastname || ''} DOB: ${m.dob || ''} PAL: ${m.pal || ''} ${m.palExpiry || ''}`; },
                        updateCount() { const el = document.getElementById('family'); if (el) el.value = this.members.length; if (typeof recalc === 'function') recalc(); },
                        // simple validation that complements jQuery Validate
                        validate() {
                            let ok = true;
                            // Don't validate if we're submitting multiple

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
                        members: { handler(){ this.updateCount(); }, deep:true } 
                    }
                }).mount('#form');

                // wire clubs/courses add buttons
                const addBtn = document.getElementById('addFamily'); if (addBtn) addBtn.addEventListener('click', function(){ window.formVm.addFamily(); });
            })();
        </script>
        <script>
            // Build a single-applicant members_json for the update flow and ensure email exists
            (function(){
                function buildAndSubmit() {
                    // gather family members from Vue state if present, otherwise from DOM
                    let family = [];
                    if (window.formVm && Array.isArray(window.formVm.members)) {
                        try { family = JSON.parse(JSON.stringify(window.formVm.members || [])); } catch(e){ family = [] }
                    }
                    if (family.length === 0) {
                        // fallback to DOM rows
                        document.querySelectorAll('.familyRow').forEach(function(row){
                            const fn = row.querySelector('.famName')?.value || '';
                            const ln = row.querySelector('.famLast')?.value || '';
                            if (!fn && !ln) return;
                            family.push({ firstname: fn, lastname: ln, pal: row.querySelector('.famPAL')?.value || '', palExpiry: row.querySelector('.famExpiry')?.value || '', dob: row.querySelector('.famDOB')?.value || '' });
                        });
                    }

                    const card = (document.getElementById('pfgaNumber')?.value || '').trim();
                    // use a synthetic email so checkout.php will accept the applicant; success email to user comes from Stripe
                    const syntheticEmail = card ? (card.replace(/\s+/g,'') + '@pfga.invalid') : ('update@pfga.invalid');

                    const app = {
                        applicationType: document.getElementById('applicationType')?.value || 'update',
                        membershipFee: document.getElementById('membershipFee')?.value || 'update',
                        firstname: document.getElementById('firstname')?.value || '',
                        lastname: document.getElementById('lastname')?.value || '',
                        email: syntheticEmail,
                        pfgaNumber: card,
                        family: family,
                        familyCount: parseInt(document.getElementById('family')?.value || '0'),
                        extra: parseInt(document.getElementById('extra')?.value || '0'),
                        terms: !!document.getElementById('terms')?.checked
                    };

                    try { document.getElementById('members_json').value = JSON.stringify([app]); } catch(e) { console.error(e); }
                    try { document.getElementById('email').value = syntheticEmail; } catch(e) { }

                    // submit the form
                    const f = document.getElementById('form');
                    if (f) f.submit();
                }

                const btn = document.getElementById('btnSubmit');
                if (btn) {
                    btn.addEventListener('click', function(e){
                        // run Vue validation if available
                        if (window.formVm && typeof window.formVm.validate === 'function') {
                            if (!window.formVm.validate()) { alert('Please fix highlighted form errors'); return; }
                        }
                        buildAndSubmit();
                    });
                }
            })();
        </script>
    </body>

</html>