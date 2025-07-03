<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
    $base_url = 'https://runo.ai';
    ?>

    <title><?php
            bloginfo('name'); ?></title>


    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">
    <!-- Core Styles -->
    <link rel="stylesheet" href="https://runo.ai/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://runo.ai/css/slicknav.min.css">
    <link rel="stylesheet" href="https://runo.ai/css/all.min.css">
    <link rel="stylesheet" href="https://runo.ai/css/custom.css">
    <link rel="stylesheet" href="https://runo.ai/css/runo.css">
    <!-- Font Awesome (CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css">
    <!-- intl-tel-input -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@17/build/css/intlTelInput.min.css" />
    <!-- JS Libraries (Defer if possible to reduce INP) -->
    <script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@17/build/js/intlTelInput.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@17/build/js/utils.js"></script>

    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-M7SSPG7W');
    </script>
    <!-- End Google Tag Manager -->
    <!-- Optional: WOW.js & Animate.css -->
    <!--
         <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
         <script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js" defer></script>
         <script>
         	document.addEventListener("DOMContentLoaded", () => {
         		new WOW().init();
         	});
         </script>
         -->
    <!-- Inline Hero Banner Style -->



    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>


    <div id="home">
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-M7SSPG7W" height="0" width="0"
                style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
        <!-- Preloader Start -->
        <div class="runo-preloader">
            <div class="runo-preloader-inner">
                <div class="runo-spinner"></div>
                <div class="runo-logo">
                    <img src="<?php echo $base_url; ?>/img/logo.png" alt="Runo Loading">
                </div>
            </div>
        </div>
        <!-- Preloader End -->
        <!-- Modal -->
        <div class="modal fade" id="requestDemoModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="custom-modal-box">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>Get in touch now!</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="demo-form" method="POST" data-page-name="Home" data-uuid="2eddbc70-045d-41ff-8866-dbf34f73f6f1">
                                <!-- Modal and variables -->
                                <!-- You can convert these to JS-based modal init if needed -->
                                <div class="mb-3">
                                    <input type="text" name="your_name" class="form-control" id="name" placeholder="Name" />
                                    <small class="text-danger d-none" id="nameError">Name is required.</small>
                                </div>
                                <div class="mb-3">
                                    <input type="email" name="your_email" class="form-control" id="email" placeholder="Email Address" />
                                    <small class="text-danger d-none" id="emailError">Valid email is required.</small>
                                </div>
                                <div class="mb-3">
                                    <input type="tel" name="your_phone" class="form-control" id="phone" placeholder="Phone Number"
                                        autocomplete="tel" />
                                    <small class="text-danger d-none" id="phoneError">Enter a valid phone number.</small>
                                </div>
                                <div class="mb-3">
                                    <select name="agents" class="form-select" id="agents">
                                        <option value="" disabled selected style="color: #858585;">Number of Calling Agents</option>
                                        <option value="50+">50+</option>
                                        <option value="11-50">11-50</option>
                                        <option value="1-10">1-10</option>
                                    </select>
                                    <small class="text-danger d-none" id="agentsError">Please select an option.</small>
                                </div>
                                <p style="font-size: 12px;">
                                    By submitting this form, I agree to the <a href="privacy-policy" target="_blank">privacy policy</a>.
                                </p>
                                <button type="submit" id="demo-form-btn" class="btn-default btn-highlighted"
                                    style="width: 100%;">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            jQuery(function($) {
                const phoneInput = document.querySelector("#phone");
                const iti = window.intlTelInput(phoneInput, {
                    initialCountry: "auto", // <-- add this line
                    geoIpLookup: function(callback) {

                        fetch('https://ipapi.co/json/')
                            .then(res => {

                                return res.json();
                            })
                            .then(data => {

                                const countryCode = (data && data.country_code) ? data.country_code.toLowerCase() : 'in';

                                callback(countryCode);
                            })
                            .catch(err => {
                                console.error("geoIpLookup error:", err);
                                callback('in');
                            });
                    },
                    separateDialCode: true,
                    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@17/build/js/utils.js",
                });

                const fakeNumbers = [
                    "1234567890", "0000000000", "1111111111", "2222222222",
                    "3333333333", "4444444444", "5555555555", "6666666666",
                    "7777777777", "8888888888", "9999999999"
                ];
                const countryLengthMap = {
                    in: [10],
                    ae: [9],
                    us: [10],
                    ca: [10],
                    gb: [10, 11],
                    sa: [9],
                    ph: [10],
                    id: [10, 11],
                    sg: [8],
                    au: [9],
                    nz: [8, 9],
                    pk: [10],
                    bd: [10],
                    lk: [9],
                    ng: [10, 11],
                    ke: [9],
                    za: [9, 10],
                    my: [9, 10],
                    th: [9, 10],
                    vn: [9, 10],
                    kr: [9, 10, 11],
                    jp: [10, 11],
                    cn: [11],
                    hk: [8],
                    tw: [9, 10],
                    de: [10, 11],
                    fr: [9, 10],
                    it: [10],
                    es: [9],
                    br: [10, 11],
                    mx: [10],
                    ar: [10, 11],
                    ru: [10],
                    tr: [10],
                    eg: [10, 11],
                    iq: [10],
                    gt: [8]
                };

                function getMaxLengthForCountry() {
                    const countryCode = iti.getSelectedCountryData().iso2;
                    const allowedLengths = countryLengthMap[countryCode];
                    return allowedLengths ? Math.max(...allowedLengths) : 15;
                }
                let phoneTouched = false;

                $("#phone").on("focus input", function() {
                    phoneTouched = true;
                    validatePhone();
                });


                phoneInput.addEventListener("input", () => {
                    phoneInput.value = phoneInput.value.replace(/\D/g, "");
                    const maxLength = getMaxLengthForCountry();
                    if (phoneInput.value.length > maxLength) {
                        phoneInput.value = phoneInput.value.slice(0, maxLength);
                    }
                    validatePhone();
                });

                phoneInput.addEventListener("countrychange", () => {
                    const maxLength = getMaxLengthForCountry();
                    phoneInput.value = phoneInput.value.slice(0, maxLength);
                    validatePhone();
                });

                function validateName() {
                    const val = $("#name").val().trim();
                    if (!val || val.length > 30) {
                        $("#nameError").removeClass("d-none");
                        return false;
                    }
                    $("#nameError").addClass("d-none");
                    return true;
                }

                function validateEmail() {
                    const val = $("#email").val().trim();
                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!val || !emailPattern.test(val)) {
                        $("#emailError").removeClass("d-none");
                        return false;
                    }
                    $("#emailError").addClass("d-none");
                    return true;
                }

                function validatePhone() {
                    const phoneNational = phoneInput.value;
                    const selectedCountry = iti.getSelectedCountryData().iso2;
                    const validLengths = countryLengthMap[selectedCountry];
                    if (!phoneTouched) {
                        $("#phoneError").addClass("d-none");
                        return false;
                    }
                    if (!iti.isValidNumber()) {
                        $("#phoneError").text("Please enter a valid phone number.").removeClass("d-none");
                        return false;
                    }

                    if (fakeNumbers.includes(phoneNational)) {
                        $("#phoneError").text("Please enter a real phone number.").removeClass("d-none");
                        return false;
                    }

                    if (validLengths && !validLengths.includes(phoneNational.length)) {
                        $("#phoneError").text(`Phone number must be ${validLengths.join(" or ")} digits.`).removeClass("d-none");
                        return false;
                    }

                    $("#phoneError").addClass("d-none");
                    return true;
                }

                function validateAgents() {
                    if (!$("#agents").val()) {
                        $("#agentsError").removeClass("d-none");
                        return false;
                    }
                    $("#agentsError").addClass("d-none");
                    return true;
                }

                $("#name").on("input", validateName);
                $("#email").on("input", validateEmail);
                $("#phone").on("input", validatePhone);
                $("#agents").on("change", validateAgents);

                $("#demo-form").on("submit", function(event) {
                    event.preventDefault();

                    const validName = validateName();
                    const validEmail = validateEmail();
                    const validPhone = validatePhone();
                    const validAgents = validateAgents();

                    if (!(validName && validEmail && validPhone && validAgents)) {
                        return;
                    }

                    const formData = $(this).serializeArray().reduce(function(obj, item) {
                        obj[item.name] = item.value;
                        return obj;
                    }, {});

                    formData["your_phone"] = iti.getNumber(); // international format
                    formData["custom_Page Name"] = window.location.pathname.replace(/^\/|\/$/g, "") || "Blogs";
                    const uuid = $(this).data("uuid");

                    submitForm("demo-form", formData, uuid);
                });
            });
        </script>
        <!-- Thank You Modal -->
        <div class="modal fade" id="thankYouModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content text-center p-4">
                    <img src="<?php echo $base_url; ?>/img/success.webp" alt="success" style="margin:10px auto; display:block;width:50px">
                    <h5 class="mb-3">Thank you!</h5>
                    <p style="margin-bottom:5px">Your submission has been received. Our team will get back to you shortly.</p>
                    <button type="button" class="btn-default btn-highlighted mt-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
        <!-- Header Start -->
        <header class="main-header">
            <div class="header-sticky">
                <nav class="navbar navbar-expand-lg">
                    <div class="container">
                        <!-- Logo Start -->
                        <a class="navbar-brand" href="/">
                            <img src="<?php echo $base_url; ?>/img/logo.png" alt="Logo" style="width: 100px;">
                        </a>
                        <!-- Logo End -->
                        <!-- Main Menu Start -->
                        <div class="collapse navbar-collapse main-menu">
                            <div class="nav-menu-wrapper">
                                <ul class="navbar-nav mr-auto" id="menu">
                                    <li class="nav-item"><a class="nav-link" href="<?php echo $base_url; ?>">Home</a></li>
                                    <li class="nav-item submenu">
                                        <a class="nav-link">Products</a>
                                        <ul class="submenu-list">
                                            <li class="nav-item"><a class="nav-link" href="<?php echo $base_url; ?>/product/call-management-crm">Call
                                                    Management CRM</a>
                                            </li>
                                            <li class="nav-item"><a class="nav-link" href="<?php echo $base_url; ?>/product/lead-management-crm">Lead
                                                    Management CRM</a>
                                            </li>
                                            <li class="nav-item"><a class="nav-link" href="<?php echo $base_url; ?>/product/auto-dialer-software">Auto
                                                    Dialer</a>
                                            </li>
                                            <li class="nav-item"><a class="nav-link" href="<?php echo $base_url; ?>/product/call-center-app">Call
                                                    Center App</a>
                                            </li>
                                            <li class="nav-item"><a class="nav-link" href="<?php echo $base_url; ?>/product/telecaller-app">Telecaller
                                                    App</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="nav-item"><a class="nav-link" href="<?php echo $base_url; ?>/pricing">Pricing</a></li>
                                    <li class="nav-item"><a class="nav-link" href="<?php echo $base_url; ?>/scontact">Contact Us</a></li>
                                    <li class="nav-item"><a class="header-btn-plain track-btn" data-label="Login - Header"
                                            href="https://web.runo.in/">Login</a></li>
                                    <li class="nav-item">
                                        <a class="header-btn track-btn" href="#" data-bs-toggle="modal" data-bs-target="#requestDemoModal"
                                            data-label="Book a Demo - Home Header">Request a
                                            Demo</a>
                                    </li>
                                    <div class="app-header-icons">
                                        <a href="https://apps.apple.com/us/app/runo-outbound-call-center-crm/id1528004506" target="_blank">
                                            <img src="<?php echo $base_url; ?>/img/app-ico.png" width="40px"></a>
                                        <a href="https://play.google.com/store/apps/details?id=in.runo.call_crm&hl=en_IN" target="_blank">
                                            <img src="<?php echo $base_url; ?>/img/play-ico.png" width="40px"></a>
                                    </div>
                                </ul>
                            </div>
                        </div>
                        <!-- Main Menu End -->
                        <div class="navbar-toggle"></div>
                    </div>
                </nav>
                <div class="responsive-menu"></div>
            </div>
        </header>
        <!-- Header End -->