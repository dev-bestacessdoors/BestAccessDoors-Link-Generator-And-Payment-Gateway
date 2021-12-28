<?php ?>
<!-- jquery -->
<script src="assets/js/jquery.js"></script>
<script src="https://js.braintreegateway.com/web/dropin/1.16.0/js/dropin.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<style>
    .input-validation {
        margin-top: -30px;
        margin-left: 10px;
    }

    input:valid~.input-validation::before {
        content: "✓";
        color: green;
    }

    textarea:valid~.input-validation::before {
        content: "✓";
        color: green;
    }

    select:valid~.input-validation::before {
        content: "✓";
        color: green;
    }

    input:invalid {
        margin-bottom: 20px;
    }

    select:invalid {
        margin-bottom: 20px;
    }

    textarea:invalid {
        margin-bottom: 20px;
    }

    .inputvalidation:invalid {
        content: "✓";
        color: green;
        border-bottom-color: '';
    }

    .invalidfield {
        border-bottom-color: 2px solid #ba281e;
    }

    .hover-underline-animation{
        display: inline-block;
        position: relative;
        color: #0087ca;
    }
</style>

<script>
    // remove this after dev
    // document.getElementById("S_email").value = 'testyaali@yopmail.com';
    // document.getElementById("Shipcompany").value = 'test';
    // document.getElementById("S_lastname").value = 'test';
    // document.getElementById("S_firstname").value = 'test';
    // document.getElementById("S_phonenumber").value = '123456789';

    // document.getElementById("notes").value = 'test';
    // document.getElementById("Shipaddress1").value = '5th Avenue';
    // document.getElementById("Shipaddress2").value = 'test';
    // document.getElementById("Shipcity").value = 'Naples';
    // document.getElementById("Shippostcode").value = '34102';
    // remove this after dev


    var controller = <?php echo json_encode($controller['Paypal_Payment_Option']) ?>;
    // $(':required').one('blur keydown', function() {
    // // $(this).addClass('inputvalidation');
    // $(this).css("border-bottom-color", "");
    // $(this).removeAttr("style");
    // if ($('#samebilling').is(":checked")){
    //   bottomcolorchnage();
    // }
    // });

    if (controller != 1 || controller != true) {
        $(".placeholder").hide();
        $(".braintree-placeholder").hide();
        $(".braintree-test-class").hide();
        $(".credit-card-area").hide();
        $(".braintree-option__paypal").hide();
        $(".braintree-large-button.braintree-toggle").hide()
    }
    var tax_class = "<?php echo $finalquote['Tax_Class'] ?: 0 ?>";
    var tax_exempt = "<?php echo $finalquote['Customer_is_Tax_Exempted'] ?>";
    var province = <?php echo $province ?>;
    var country = <?php echo "\"" . $Shipcountry . "\""; ?>;
    if (country != "") {
        if (country == 'US') {
            country = 'United States';
        } else if (country == 'CA') {
            country = 'Canada';
        }
        document.getElementById("Shipcountry").value = country;
        var shipvalue = <?php echo "\"" . $Shipstate . "\""; ?>;
        var scope = 'Shipstate';
        show_province(country, scope, shipvalue);
    }

    var bcountry = <?php echo "\"" . $Billcountry . "\""; ?>;
    if (bcountry != "") {
        if (bcountry == 'US') {
            bcountry = 'United States';
        } else if (bcountry == 'CA') {
            bcountry = 'Canada';
        }
        document.getElementById("country").value = bcountry;
        var shipvalue = <?php echo "\"" . $Billstate . "\""; ?>;
        var scope = 'state';
        show_province(bcountry, scope, shipvalue);
    }

    var province_curr;

    function show_province(countryvalue, scope, shipvalue) {
        // console.log("value:" + countryvalue + "\nscope:" + scope + "\nshipvalue:" + shipvalue);
        if (scope == 'state') {
            province_based_country = 'country';
        } else if (scope == 'Shipstate') {
            province_based_country = 'Shipcountry';
        }

        document.getElementById(scope).innerHTML = '';
        province_curr = province[countryvalue];
        if (province_curr != null) {
            // console.log("Load Province ");
            var fetchcallcount = 0;
            for (var k = province_curr.length - 1; k >= 0; k--) {
                var opt = document.createElement("option");
                document.getElementById(scope).innerHTML += '<option id="' + (k + 1) + '" value="' + province_curr[k].Province + '">' + province_curr[k].Province + '</option>';
                $('#' + scope).css("border-bottom-color", "");

                shipvalue = shipvalue.charAt(0).toUpperCase() + shipvalue.slice(1).toLowerCase();
                if (shipvalue != "") {
                    if (shipvalue.toLowerCase() == province_curr[k].Province.toLowerCase()) {
                        fetchcallcount = fetchcallcount + 1;
                    }
                } else {
                    fetchcallcount = fetchcallcount + 1;
                }
            }
            if (shipvalue == "") {
                shipvalue = document.getElementById(scope).value;
            }
            if (shipvalue != "") {
                if (shipvalue.length <= 2) {
                    shipvalue = shipvalue.toUpperCase();
                    var short_ptovince = province_curr.filter(function(obj) {
                        if (obj.Province_Short_Name == shipvalue) {
                            return obj;
                        }
                    });
                    if (short_ptovince != "") {
                        shipvalue = short_ptovince[0]['Province'];
                        province_based_country = short_ptovince[0]['Country']
                    }
                }
                shipvalue = shipvalue.replace(/\b[a-z]/g, (x) => x.toUpperCase());
                document.getElementById(scope).value = shipvalue;
                var provinceval = document.getElementById(scope).value;

                if (fetchcallcount > 0 && scope != 'state') {
                    fetch_province(provinceval, 'Shipcountry');
                }
            }
            if (countryvalue == null || countryvalue == undefined) {
                document.getElementById(countryvalue).value = province_based_country;
                show_province(province_based_country, scope, shipvalue);
            }

        } else {
            if (scope != 'state') {
                settaxtclass("");
                caltotal();
            }

        }
    }

    function fetch_province(provinceval, countryscope) {
        countryval = document.getElementById(countryscope).value;
        province_curr = province[countryval];
        var getprovinceobj = province_curr.filter(function(obj) {
            if (obj.Province == provinceval) {
                return obj;
            }
        });
        var get_tax_class = "";
        if (getprovinceobj[0]['Total_Tax_Rate'] != "") {
            get_tax_class = getprovinceobj[0]['Total_Tax_Rate'];
            settaxtclass(get_tax_class);
            caltotal();
        } else {
            settaxtclass(get_tax_class);
            fetchavatax('Shippostcode');
            caltotal();
        }
    }

    function settaxtclass(get_tax_class) {
        tax_class = get_tax_class;
        document.getElementById('taxclass').value = tax_class;
    }


    var storename = '<?php echo $storename; ?>';

    //additional shipping charge validations
    $('#add1').click(function() {
        if ($(this).is(':checked')) {
            $("#add2").prop("checked", false);
            $("#add3").prop("checked", false);
            $("#add4").prop("checked", false);
            $("#add5").prop("checked", false);
        }
    });
    $('#add2').click(function() {
        if ($(this).is(':checked')) {
            $("#add1").prop("checked", false);
        }
    });
    $('#add3').click(function() {
        if ($(this).is(':checked')) {
            $("#add1").prop("checked", false);
        }
    });
    $('#add4').click(function() {
        if ($(this).is(':checked')) {
            $("#add1").prop("checked", false);
        }
    });
    $('#add5').click(function() {
        if ($(this).is(':checked')) {
            $("#add1").prop("checked", false);
        }
    });

    <?php if (isset($quoteamount)) { ?>
        document.getElementById("quotecost").innerHTML = '<?php echo "$ " . number_format((float)$quoteamount, 2, '.', ''); ?>';
        caltotal();
    <?php } ?>

    <?php if (isset($quotenumber)) { ?>
        document.getElementById("quotenotxt").innerHTML = '<?php echo $quotenumber; ?>';
        getaddship();
        caltotal();
    <?php } ?>

    $("input#quote").on({
        keydown: function(e) {
            if (e.which === 32)
                return false;
        },
        change: function() {
            this.value = this.value.replace(/\s/g, "");
        }
    });

    function appenddecimal() {
        var procost = document.getElementById("procost").value;
        if (procost != "") {
            document.getElementById("quotecost").innerHTML = "$ " + parseFloat(procost).toFixed(2);
            document.getElementById("procost").value = parseFloat(procost).toFixed(2);
        } else {
            document.getElementById("quotecost").innerHTML = "$ 0.00";
        }
    }

    $('#quote').keyup(function() {
        $(this).val($(this).val().toUpperCase());
        document.getElementById("agreequoteno").innerHTML = document.getElementById("quote").value;
        document.getElementById("quotenotxt").innerHTML = document.getElementById("quote").value;
    });

    function getaddship(checkboxElem) {
        if ($('#add1').prop("checked") == false && $('#add2').prop("checked") == false && $('#add3').prop("checked") == false && $('#add4').prop("checked") == false && $('#add5').prop("checked") == false) {
            $("#add1").prop("checked", true);
        }
        var val1, val2, val3, val4, val5, shippingcost, totalamount;
        if ($('#add1').is(":checked")) {
            val1 = $('#add1').val();
        } else {
            val1 = 0;
        }
        if ($('#add2').is(":checked")) {
            val2 = $('#add2').val();
        } else {
            val2 = 0;
        }
        if ($('#add3').is(":checked")) {
            val3 = $('#add3').val();
        } else {
            val3 = 0;
        }
        if ($('#add4').is(":checked")) {
            val4 = $('#add4').val();
        } else {
            val4 = 0;
        }
        if ($('#add5').is(":checked")) {
            val5 = $('#add5').val();
        } else {
            val5 = 0;
        }
        shippingcost = parseInt(val1) + parseInt(val2) + parseInt(val3) + parseInt(val4) + parseInt(val5);
        if (shippingcost > 0) {
            document.getElementById("shipingcost").innerHTML = "$ " + parseFloat(shippingcost).toFixed(2);
        } else {
            document.getElementById("shipingcost").innerHTML = "$0.00";
        }
    }

    $('.quotecost').on('DOMSubtreeModified', function() {
        caltotal();
    });
    $('.shipingcost').on('DOMSubtreeModified', function() {
        caltotal();
    });

    function caltotal() {

        var quotecost = document.getElementById("quotecost").innerHTML;
        quotecost = quotecost.replace(/^\D+/g, '');

        var shippingcost = document.getElementById("shipingcost").innerHTML;
        shippingcost = shippingcost.replace(/^\D+/g, '');

        tax = document.getElementById("tax").innerHTML;
        tax = tax.replace(/\,/g, '');
        tax = tax.replace(/^\D+/g, '');

        if (quotecost == "") {
            var totalamount = 0;
            document.getElementById("total").innerHTML = "$ 0.00";
            document.getElementById("amount").value = 0;
            document.getElementById("agreetotal").innerHTML = "$0.00";
        } else if (quotecost != "") {
            Tax_Class = document.getElementById('taxclass').value;
            if (Tax_Class == '') {
                Tax_Class = "<?php echo $finalquote['Tax_Class'] ?>";
                Tax_Class = tax_class;
            }
            // var Tax_Class = tax_class;
            var currency = "<?php echo $currency ?>";
            var current_country = document.getElementById("Shipcountry").value;
            if (Tax_Class != "") {
                var overal_subtotal = parseFloat(quotecost) + parseFloat(shippingcost);
                tax = (overal_subtotal * Tax_Class) / 100;
                var totalamount = overal_subtotal + parseFloat(tax);
                document.getElementById("taxtxt").innerHTML = "Tax ( " + Tax_Class + "% )";
                document.getElementById("tax").innerHTML = "$ " + parseFloat(tax).toFixed(2);
                document.getElementById("subtotal").innerHTML = "$ " + parseFloat(overal_subtotal).toFixed(2);
                document.getElementById("total").innerHTML = "$ " + parseFloat(totalamount).toFixed(2);
                document.getElementById("amount").value = parseFloat(totalamount).toFixed(2);
                document.getElementById("agreetotal").innerHTML = "$" + parseFloat(totalamount).toFixed(2);
            } else {
                var overal_subtotal = parseFloat(quotecost) + parseFloat(shippingcost);
                var totalamount = overal_subtotal;
                document.getElementById("taxtxt").innerHTML = '';
                document.getElementById("tax").innerHTML = '';
                document.getElementById("subtotal").innerHTML = "$ " + parseFloat(overal_subtotal).toFixed(2);
                document.getElementById("total").innerHTML = "$ " + parseFloat(totalamount).toFixed(2);
                document.getElementById("amount").value = parseFloat(totalamount).toFixed(2);
                document.getElementById("agreetotal").innerHTML = "$" + parseFloat(totalamount).toFixed(2);
            }

        } else {
            document.getElementById("tax").innerHTML.display = 'none';
            document.getElementById("taxtxt").innerHTML.display = 'none';
            document.getElementById("subtotal").innerHTML = "$ " + parseFloat(overal_subtotal).toFixed(2);
            document.getElementById("total").innerHTML = "$ " + parseFloat(overal_subtotal).toFixed(2);
            document.getElementById("amount").value = parseFloat(overal_subtotal).toFixed(2);
            document.getElementById("agreetotal").innerHTML = "$" + parseFloat(overal_subtotal).toFixed(2);
        }
    }

    var missingfieldnames = [];
    $('#agree').click(function() {

        //rpr -test
        var Store_payment_gateway = '<?php echo $store_payment_gateway; ?>';
        console.log("payment method ", Store_payment_gateway);

        document.getElementById("fullname").innerHTML = document.getElementById("c_firstname").value + " " + document.getElementById("c_lastname").value;
        if ($(this).is(':checked')) {
            var missinglist = [];
            var formElements = new Array();

            $("form :input").each(function() {
                formElements.push($(this));
            });
            $.each(formElements, function(i, formElement) {
                var fieldValue = formElement[0].value;
                if (fieldValue == 0) {
                    missinglist.push(formElement[0].name);
                    missingfieldnames.push(formElement[0].name);
                }
            });

            function removeA(arr) {
                var what, a = arguments,
                    L = a.length,
                    ax;
                while (L > 1 && arr.length) {
                    what = a[--L];
                    while ((ax = arr.indexOf(what)) !== -1) {
                        arr.splice(ax, 1);
                    }
                }
                return arr;
            }
            removeA(missinglist, 'btnverzenden');
            removeA(missinglist, 'payment_method_nonce');
            removeA(missinglist, 'addval1');
            removeA(missinglist, 'address2');
            removeA(missinglist, 'Shipaddress2');
            removeA(missinglist, 'company');
            removeA(missinglist, 'Shipcompany');
            removeA(missinglist, 'notes');
            removeA(missinglist, '');

            removeA(missingfieldnames, 'btnverzenden');
            removeA(missingfieldnames, 'payment_method_nonce');
            removeA(missingfieldnames, 'addval1');
            removeA(missingfieldnames, 'address2');
            removeA(missingfieldnames, 'Shipaddress2');
            removeA(missingfieldnames, 'company');
            removeA(missingfieldnames, 'Shipcompany');
            removeA(missingfieldnames, 'notes');
            removeA(missingfieldnames, '');

            for (let i = 0; i < missinglist.length; i++) {
                $('#' + missinglist[i]).css("border-bottom-color", "#f00");
            }

            var index = missinglist.indexOf("c_firstname");
            if (~index) {
                missinglist[index] = "\nBilling First Name";
            }
            var index = missinglist.indexOf("c_lastname");
            if (~index) {
                missinglist[index] = "\nBilling Last Name";
            }
            var index = missinglist.indexOf("c_email");
            if (~index) {
                missinglist[index] = "\nBilling Email Id";
            }
            var index = missinglist.indexOf("c_phonenumber");
            if (~index) {
                missinglist[index] = "\nBilling Phone Number";
            }
            var index = missinglist.indexOf("address1");
            if (~index) {
                missinglist[index] = "\nBilling Address1";
            }
            var index = missinglist.indexOf("address2");
            if (~index) {
                missinglist[index] = "\nBilling Address2";
            }
            var index = missinglist.indexOf("city");
            if (~index) {
                missinglist[index] = "\nBilling City";
            }
            var index = missinglist.indexOf("state");
            if (~index) {
                missinglist[index] = "\nBilling State";
            }
            var index = missinglist.indexOf("postcode");
            if (~index) {
                missinglist[index] = "\nBilling Postcode";
            }
            var index = missinglist.indexOf("country");
            if (~index) {
                missinglist[index] = "\nBilling Country";
            } //
            var index = missinglist.indexOf("S_firstname");
            if (~index) {
                missinglist[index] = "\nShipping First Name";
            }
            var index = missinglist.indexOf("S_lastname");
            if (~index) {
                missinglist[index] = "\nShipping Last Name";
            }
            var index = missinglist.indexOf("S_email");
            if (~index) {
                missinglist[index] = "\nShipping Email Id";
            }
            var index = missinglist.indexOf("S_phonenumber");
            if (~index) {
                missinglist[index] = "\nShipping Phone Number";
            }
            var index = missinglist.indexOf("Shipaddress1");
            if (~index) {
                missinglist[index] = "\nShipping Address1";
            }
            var index = missinglist.indexOf("Shipaddress2");
            if (~index) {
                missinglist[index] = "\nShipping Address2";
            }
            var index = missinglist.indexOf("Shipcity");
            if (~index) {
                missinglist[index] = "\nShipping City";
            }
            var index = missinglist.indexOf("Shipstate");
            if (~index) {
                missinglist[index] = "\nShipping State";
            }
            var index = missinglist.indexOf("Shippostcode");
            if (~index) {
                missinglist[index] = "\nShipping Postcode";
            }
            var index = missinglist.indexOf("Shipcountry");
            if (~index) {
                missinglist[index] = "\nShipping Country";
            }

            if (missinglist == "") {
                if (controller != 1 || controller != true) {
                    $(".braintree-option__paypal").hide();
                    $(".credit-card-area").show();
                    $(".braintree-option__card").show();
                    // $(".braintree-show-card").show();
                    $(".braintree-placeholder").hide();
                    $(".braintree-toggle").hide();
                    document.getElementById("paymenthead").style.display = "block";
                }

                billingdisable();
                shippingdisable();
                document.getElementById('add1').disabled = true;
                document.getElementById('add2').disabled = true;
                document.getElementById('add3').disabled = true;
                document.getElementById('add4').disabled = true;
                document.getElementById('add5').disabled = true;
                document.getElementById('notes').disabled = true;
                document.getElementById('samebilling').disabled = true;
                document.getElementById("billing").style.display = "block";
                document.getElementById("arrow").style.display = "none";
                $("arrow").hide();
                document.getElementById("Billingtext").innerHTML = "To adjust Billing address, un-check the agree box";

                // if all the form is valid - enable button
                if (Store_payment_gateway == 'Stripe') {
                    document.getElementById("stripepaynow").style.display = "block";
                    document.getElementById("stripe_card").style.display = "block";
                }else{
                    document.getElementById("stripepaynow").style.display = "none";
                    document.getElementById("stripe_card").style.display = "none";
                }

                if (Store_payment_gateway == 'Braintree') {
                    document.getElementById("btnverzenden").style.display = "block";
                }
            } else {

                // console.log('missingfieldnames',missingfieldnames);
                // $.each(missingfieldnames, function(i, formElement) {
                //   $('#'+formElement).css("border-bottom-color", "#f00");
                //   if ($('#samebilling').is(":checked")){
                //     bottomcolorchnage();
                //   }
                // });
                // missingfieldnames =[];
                alert("Please fill in the following fields:" + missinglist);
                $("#agree").prop("checked", false);
            }
        } else {
            console.log("diagree");
            if (controller != 1 || controller != true) {
                $(".braintree-option").hide();
                $(".credit-card-area").hide();
                $(".braintree-show-card").hide();
                $(".braintree-option__card").hide();
                $(".placeholder").hide();
                $(".braintree-placeholder").hide();
            }
            $("#samebilling").prop("checked", false);
            document.getElementById("Billingtext").innerHTML = "My Billing address is the same as my Shipping address";
            document.getElementById("arrow").style.display = "";
            document.getElementById("stripe_card").style.display = "none";
            billingenable();
            shippingenable();
            document.getElementById('add1').disabled = false;
            document.getElementById('add2').disabled = false;
            document.getElementById('add3').disabled = false;
            document.getElementById('add4').disabled = false;
            document.getElementById('add5').disabled = false;
            document.getElementById('notes').disabled = false;
            document.getElementById('samebilling').disabled = false;
            $("#agree").prop("checked", false);
        }
    });

    //pay by stripe
    $('#stripepaynow').click(function(event) {
        document.getElementById('stripepaynow').disabled = true;
        event.preventDefault();
        var bodyobj = getformdata('stripe');
        stripe.createToken(card).then(function(result) {
            if (result.error) {
                var errorElement = document.getElementById('card-errors');
                errorElement.textContent = result.error.message;
            } else {
                document.getElementById('loading').style.display = 'block';
                var token = result.token;
                bodyobj['token'] = token.id;
                bodyobj['Stripe_custid'] = '<?php echo $finalquote['Stripe_Customer_Id']; ?>';
                $.ajax({
                    url: "stripe_checkout.php?s=<?php echo $storename; ?>", //the page containing php script
                    type: "post", //request type,
                    dataType: 'json',
                    data: bodyobj,
                    success: function(result) {
                        if (result.error == false) {
                            window.location = result.redirect;
                            console.log("transaction success");
                            $(".braintree-large-button.braintree-toggle").hide();
                            document.getElementById('loading').style.display = 'none';
                        } else {
                            document.getElementById('loading').style.display = 'none';
                            console.log("transaction failed");
                            document.getElementById("modaloverlay").style.display = 'block';
                            var modal = document.getElementById("myModal");
                            modal.style.display = "block";
                            document.getElementById("tranError").innerHTML = result.message;
                            console.log(result.message);
                            document.getElementById('stripepaynow').disabled = false;
                        }
                    }
                });
            }
        })
    });



    //get form data
    function getformdata(scope) {
        var post_dataObject = new Object();
        var quotecost = document.getElementById("quotecost").innerHTML;
        quotecost = quotecost.replace(/^\D+/g, '');
        var shippingcost = document.getElementById("shipingcost").innerHTML;
        shippingcost = shippingcost.replace(/^\D+/g, '');
        var Tax_CAD = null,
            Tax_Class = null;
        var current_country = document.getElementById("Shipcountry").value;
        if (tax_class != "") {
            Tax_Class = tax_class;
            tax = document.getElementById("tax").innerHTML;
            tax = tax.replace(/\,/g, '');
            Tax_CAD = tax.replace(/^\D+/g, '');
            var overal_subtotal = parseFloat(quotecost) + parseFloat(shippingcost);
            // tax = (overal_subtotal * Tax_Class )/100;
            var totalamount = overal_subtotal + parseFloat(Tax_CAD);
        } else {
            var overal_subtotal = parseFloat(quotecost) + parseFloat(shippingcost);
            var totalamount = overal_subtotal;
        }

        //additional  shipping list
        var addtionalshippingarray = [];
        if ($('#add1').is(":checked")) {
            addtionalshippingarray.push('No Additional Shipping');
        }
        if ($('#add2').is(":checked")) {
            addtionalshippingarray.push('Construction Site + $80');
        }
        if ($('#add3').is(":checked")) {
            addtionalshippingarray.push('Call Before Delivery + $40');
        }
        if ($('#add4').is(":checked")) {
            addtionalshippingarray.push('Lift Gate + $100');
        }
        if ($('#add5').is(":checked")) {
            addtionalshippingarray.push('Delivery Appointment With 4 Hour Window + $150');
        }
        addtionalshipping = addtionalshippingarray.toString();

        var payrecdid = "<?php echo $paymentformrecordid ?>";
        post_dataObject.payrecdid = btoa(payrecdid);

        post_dataObject.amount = document.getElementById("amount").value;
        post_dataObject.quote = document.getElementById("quote").value;
        post_dataObject.quotecost = quotecost;
        post_dataObject.Tax_Class = Tax_Class;
        post_dataObject.Tax_CAD = Tax_CAD;
        post_dataObject.shippingcost = shippingcost;
        post_dataObject.addtionalshipping = addtionalshipping;
        post_dataObject.overal_subtotal = overal_subtotal;

        post_dataObject.store = '<?php echo $storename; ?>';
        post_dataObject.currency = '<?php echo $currency; ?>';

        post_dataObject.customer_firstname = document.getElementById("c_firstname").value;
        post_dataObject.customer_lastname = document.getElementById("c_lastname").value;
        post_dataObject.customer_email = document.getElementById("c_email").value;
        post_dataObject.customer_phonenumber = document.getElementById("c_phonenumber").value;
        post_dataObject.Bcompanyname = document.getElementById("company").value;
        post_dataObject.Baddress1 = document.getElementById("address1").value;
        post_dataObject.Baddress2 = document.getElementById("address2").value;
        post_dataObject.Bcity = document.getElementById("city").value;
        post_dataObject.Bstate = document.getElementById("state").value;
        post_dataObject.Bpostcode = document.getElementById("postcode").value;
        post_dataObject.Bcountry = document.getElementById("country").value;

        post_dataObject.Ship_firstname = document.getElementById("S_firstname").value;
        post_dataObject.Ship_lastname = document.getElementById("S_lastname").value;
        post_dataObject.Ship_email = document.getElementById("S_email").value;
        post_dataObject.Ship_phonenumber = document.getElementById("S_phonenumber").value;
        post_dataObject.S_company = document.getElementById("Shipcompany").value;
        post_dataObject.Saddress1 = document.getElementById("Shipaddress1").value;
        post_dataObject.Saddress2 = document.getElementById("Shipaddress2").value;
        post_dataObject.Scity = document.getElementById("Shipcity").value;
        post_dataObject.Sstate = document.getElementById("Shipstate").value;
        post_dataObject.Spostcode = document.getElementById("Shippostcode").value;
        post_dataObject.Scountry = document.getElementById("Shipcountry").value;
        post_dataObject.S_notes = document.getElementById("notes").value;
        return post_dataObject;
    };


    $('#c_firstname').on('change keyup paste', function() {
        document.getElementById("fullname").innerHTML = document.getElementById("c_firstname").value + " " + document.getElementById("c_lastname").value;
        $(this).css("border-bottom-color", "");
    });
    $('#c_lastname').on('change keyup paste', function() {
        document.getElementById("fullname").innerHTML = document.getElementById("c_firstname").value + " " + document.getElementById("c_lastname").value;
        $(this).css("border-bottom-color", "");
    });


    $('#S_firstname').on('change blur paste', function() {
        $(this).css("border-bottom-color", "");
        document.getElementById("fullname").innerHTML = document.getElementById("c_firstname").value + " " + document.getElementById("c_lastname").value;
    });
    $('#S_lastname').on('change blur paste', function() {
        $(this).css("border-bottom-color", "");
        document.getElementById("fullname").innerHTML = document.getElementById("c_firstname").value + " " + document.getElementById("c_lastname").value;
    });

    $('#c_email').on('change blur paste', function() {
        $(this).css("border-bottom-color", "");
    });
    $('#c_phonenumber').on('change blur paste', function() {
        $(this).css("border-bottom-color", "");
    });
    $('#address1').on('change keyup paste', function() {
        $(this).css("border-bottom-color", "");
    });
    $('#address2').on('change keyup paste', function() {
        $(this).css("border-bottom-color", "");
    });
    $('#city').on('change keyup paste', function() {
        $(this).css("border-bottom-color", "");
    });
    $('#state').on('change keyup paste', function() {
        $(this).css("border-bottom-color", "");
    });
    $('#postcode').on('change keyup paste', function() {
        $(this).css("border-bottom-color", "");
    });
    $('#country').on('change keyup paste', function() {
        $(this).css("border-bottom-color", "");
    });
    $('#address1').on('change keyup paste', function() {
        $(this).css("border-bottom-color", "");
    });
    $('#address2').on('change keyup paste', function() {
        $(this).css("border-bottom-color", "");
    });
    $('#city').on('change keyup paste', function() {
        $(this).css("border-bottom-color", "");
    });
    $('#state').on('change keyup paste', function() {
        $(this).css("border-bottom-color", "");
    });
    $('#postcode').on('change keyup paste', function() {
        $(this).css("border-bottom-color", "");
    });
    $('#country').on('change keyup paste', function() {
        $(this).css("border-bottom-color", "");
    });

    $('#S_email').on('change keyup paste', function() {
        $(this).css("border-bottom-color", "");
    });
    $('#S_phonenumber').on('change keyup paste', function() {
        $(this).css("border-bottom-color", "");
    });
    $('#Shipaddress1').on('change keyup paste', function() {
        $(this).css("border-bottom-color", "");
    });
    $('#Shipaddress2').on('change keyup paste', function() {
        $(this).css("border-bottom-color", "");
    });
    $('#Shipcity').on('change keyup paste', function() {
        $(this).css("border-bottom-color", "");
    });
    $('#Shipstate').on('change keyup paste', function() {
        $(this).css("border-bottom-color", "");
    });
    $('#Shippostcode').on('change keyup paste', function() {
        $(this).css("border-bottom-color", "");
    });
    $('#Shipcountry').on('change keyup paste', function() {
        $(this).css("border-bottom-color", "");
    });


    $('.submit-btn').css('text-transform', '');
    $("#hideamount").hide();
    var form = document.querySelector('#payment-form');
    //get gateway token
    var client_token = "<?php echo ($gateway->ClientToken()->generate()); ?>";
    braintree.dropin.create({
        authorization: client_token,
        selector: '#bt-dropin',
        paypal: {
            flow: 'vault'
        }
    }, function(createErr, instance) {
        if (createErr) {
            // console.log('Create Error', createErr);
            return;
        }

        form.addEventListener('submit', function(event) {
            console.log('add event Listener called ');

            event.preventDefault();
            instance.requestPaymentMethod(function(err, payload) {
                if (err) {
                    console.log('Request Payment Method Error', err);
                    return;
                }
                document.getElementById('loading').style.display = 'block';
                $("#btnverzenden").hide();
                $("#btnverzenden2").show();
                $("#btnverzenden3").show();
                $(".braintree-large-button.braintree-toggle").hide()
                document.getElementById('agree').disabled = true;

                bodyobj = getformdata('braintree');
                var form = document.getElementById('payment-form');
                document.querySelector('#nonce').value = payload.nonce;
                var nonce = payload.nonce;
                bodyobj['payment_method_nonce'] = nonce;
                // Add the nonce to the form and submit
                // {    quote:quote, amount:amount, quotecost:quotecost, Tax_Class:Tax_Class, Tax_CAD:Tax_CAD, shippingcost:shippingcost, addtionalshipping:addtionalshipping, customer_firstname: C_firstname, customer_lastname: C_lastname, customer_email: C_email, customer_phonenumber: C_phonenumber, store: "<?php echo $storename; ?>",currency: "<?php echo $currency; ?>",
                //       Bcompanyname: C_company, Baddress1: B_address1, Baddress2: B_address2, Bcity: B_city, Bstate: B_state, Bpostcode:B_postcode,Bcountry: B_country, Ship_firstname: S_firstname, Ship_lastname: S_lastname, Ship_email: S_email, Ship_phonenumber: S_phonenumber,S_company: S_company, Saddress1: S_address1, Saddress2: S_address2, Scity: S_city, Sstate:S_state , Spostcode: S_postcode,Scountry: S_country,S_notes:S_notes, payment_method_nonce:nonce, payrecdid:payrecdid
                //     }
                $.ajax({
                    url: "pay_checkout.php?s=<?php echo $storename; ?>", //the page containing php script
                    type: "post", //request type,
                    dataType: 'json',
                    data: bodyobj,
                    success: function(result) {
                        $('.braintree-method').css('border-color', 'green');
                        $('.braintree-method__icon').show();
                        $("#btnverzenden2").hide();
                        $("#btnverzenden3").hide();
                        if (result.error == false) {
                            window.location = result.redirect;
                            console.log("transaction success");
                            $(".braintree-large-button.braintree-toggle").hide();
                            document.getElementById('loading').style.display = 'none';
                        } else {
                            document.getElementById('loading').style.display = 'none';
                            $('.braintree-method').css('border-color', 'red');
                            $(".braintree-method__icon").hide();
                            $(".braintree-large-button.braintree-toggle").hide();
                            console.log("transaction failed");
                            document.getElementById("modaloverlay").style.display = 'block';
                            var modal = document.getElementById("myModal");
                            modal.style.display = "block";
                            document.getElementById("tranError").innerHTML = result.message;
                            console.log(result.message);
                        }
                    }
                });
                // form.submit();
            });
        });
    });

    //get id of the selected element
    function get_id(clicked_id) {
        if ("closeErrorModal" == clicked_id) {
            var modal = document.getElementById("myModal");
            // Get the <span> element that closes the modal
            modal.style.display = "none";
            document.getElementById("modaloverlay").style.display = "none";
            // 		setTimeout("location.reload(true);",100);
        }
    };

    //onload disable shipping details
    $("#samebilling").prop("checked", true);
    if ($('#samebilling').is(":checked")) {
        billingdisable();
    }

    $("#shipping").change(function() {
        if ($('#samebilling').is(":checked")) {
            // console.log("function call");
            SetBilling();
        }
    });

    function phonevalidate(event) {
        var num = document.getElementById(event.target.id).value;
        if (isNaN(num)) {
            alert("Enter Valid Phone Number...");
            $('#' + event.target.id).css("border-bottom-color", "red");
            return false;
        } else {
            $('#' + event.target.id).css("border-bottom-color", "");
            return true;
        }
    }

    function validateEmail(emailField) {
        var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
        if (reg.test(emailField.value) == false) {
            alert("Enter valid Email Id ");
            $(this).css("border-bottom-color", "red");
            return false;
        }
        $(this).css("border-bottom-color", "");
        return true;
    }


    var timeinterval;

    function fieldupdate() {
        timeinterval = setInterval(shippingfetch, 1000);
    }
    document.getElementById("fullname").innerHTML = document.getElementById("c_firstname").value + " " + document.getElementById("c_lastname").value;

    function SetBilling(checked) {
        document.getElementById("fullname").innerHTML = document.getElementById("c_firstname").value + " " + document.getElementById("c_lastname").value;
        if ($('#samebilling').is(":checked")) {
            // console.log("billing if");
            billingenable();
            shippingfetch();
            billingdisable();
            document.getElementById("billing").style.display = 'none';
            document.getElementById("Billingtext").innerHTML = "Unselect to change Billing Details";
        } else {
            // console.log("billing else");
            document.getElementById("billing").style.display = 'block';
            document.getElementById("Billingtext").innerHTML = "My Billing address is the same as my Shipping address";
            billingenable();
        }
    }

    function shippingfetch() {
        clearInterval(timeinterval);
        document.getElementById('c_firstname').value = document.getElementById("S_firstname").value;
        document.getElementById('c_lastname').value = document.getElementById('S_lastname').value;
        document.getElementById('c_email').value = document.getElementById('S_email').value;
        document.getElementById('c_phonenumber').value = document.getElementById('S_phonenumber').value;
        document.getElementById('company').value = document.getElementById('Shipcompany').value;
        document.getElementById('address1').value = document.getElementById("Shipaddress1").value;
        document.getElementById('address2').value = document.getElementById('Shipaddress2').value;
        document.getElementById('city').value = document.getElementById('Shipcity').value;
        var scountry = document.getElementById('Shipcountry').value;
        document.getElementById('country').value = scountry;
        var scope = 'state';
        // console.log(scountry+"::"+scope +"::"+ document.getElementById('Shipstate').value);
        show_province(scountry, scope, document.getElementById('Shipstate').value);
        document.getElementById('state').value = document.getElementById('Shipstate').value;
        document.getElementById('postcode').value = document.getElementById('Shippostcode').value;
        document.getElementById('country').value = document.getElementById('Shipcountry').value;
        bottomcolorchnage();

    }

    function shippingdisable() {
        document.getElementById('S_firstname').disabled = true;
        document.getElementById('S_lastname').disabled = true;
        document.getElementById('S_email').disabled = true;
        document.getElementById('S_phonenumber').disabled = true;
        document.getElementById('Shipcompany').disabled = true;
        document.getElementById('Shipaddress1').disabled = true;
        document.getElementById('Shipaddress2').disabled = true;
        document.getElementById('Shipcity').disabled = true;
        document.getElementById('Shipstate').disabled = true;
        document.getElementById('Shippostcode').disabled = true;
        document.getElementById('Shipcountry').disabled = true;
    }

    function shippingenable() {
        document.getElementById('S_firstname').disabled = false;
        document.getElementById('S_lastname').disabled = false;
        document.getElementById('S_email').disabled = false;
        document.getElementById('S_phonenumber').disabled = false;
        document.getElementById('Shipcompany').disabled = false;
        document.getElementById('Shipaddress1').disabled = false;
        document.getElementById('Shipaddress2').disabled = false;
        document.getElementById('Shipcity').disabled = false;
        document.getElementById('Shipstate').disabled = false;
        document.getElementById('Shippostcode').disabled = false;
        document.getElementById('Shipcountry').disabled = false;
    }

    function billingenable() {
        document.getElementById("c_firstname").disabled = false;
        document.getElementById('c_lastname').disabled = false;
        document.getElementById('c_email').disabled = false;
        document.getElementById('c_phonenumber').disabled = false;
        document.getElementById('company').disabled = false;
        document.getElementById("address1").disabled = false;
        document.getElementById('address2').disabled = false;
        document.getElementById('city').disabled = false;
        document.getElementById('state').disabled = false;
        document.getElementById('postcode').disabled = false;
        document.getElementById('country').disabled = false;
    }

    function billingdisable() {
        document.getElementById("c_firstname").disabled = true;
        document.getElementById('c_lastname').disabled = true;
        document.getElementById('c_email').disabled = true;
        document.getElementById('c_phonenumber').disabled = true;
        document.getElementById('company').disabled = true;
        document.getElementById("address1").disabled = true;
        document.getElementById('address2').disabled = true;
        document.getElementById('city').disabled = true;
        document.getElementById('state').disabled = true;
        document.getElementById('postcode').disabled = true;
        document.getElementById('country').disabled = true;
    }

    function bottomcolorchnage() {
        $('#c_firstname').css("border-bottom-color", "");
        $('#c_lastname').css("border-bottom-color", "");
        $('#c_email').css("border-bottom-color", "");
        $('#c_phonenumber').css("border-bottom-color", "");
        $('#company').css("border-bottom-color", "");
        $('#c_lastname').css("border-bottom-color", "");
        $('#address1').css("border-bottom-color", "");
        $('#address2').css("border-bottom-color", "");
        $('#city').css("border-bottom-color", "");
        $('#state').css("border-bottom-color", "");
        $('#postcode').css("border-bottom-color", "");
        $('#country').css("border-bottom-color", "");
    }

    $("Shipstate").change(function(){ fetchavatax('Shippostcode') });
    $("Shipcity").change(function(){ fetchavatax('Shippostcode') });
    $("Shippostcode").change(function(){ fetchavatax('Shippostcode') });

     /** Calculate Shipping tax for US stores */
     function fetchavatax($eid){
        var zipcode = document.getElementById($eid).value;
        var shipcountry = document.getElementById('Shipcountry').value;
        var shipstate = document.getElementById('Shipstate').value;
        var Shipcity = document.getElementById('Shipcity').value;
        var avaurl = 'https://door-pay.com/p/AvalaraCORSWorkaround.php?zipcode=' + zipcode + '&Shipcity=' + Shipcity +'&shipstate='+ shipstate  ;
        console.log("zipcode:" + tax_exempt);
        if (Shipcountry && shipcountry == "United States" && zipcode != '' && Shipcity != '' && shipstate != '' && tax_exempt == false) {
            $.ajax({
                url: avaurl,
                type: 'GET',
                "crossDomain": true,
                success: function(result) {
                    let json_result = JSON.parse(result);
                    if (json_result.code != 200)
                    {
                        // console.log("failed to get Avatax");
                        document.getElementById('taxclass').value = '';
                        if (json_result.info.hasOwnProperty('zipcode_by_city') &&  json_result.info['zipcode_by_city'].length > 0)
                        {
                            document.getElementById($eid).value = '';
                            var avaiable_zipcodes = '';
                            var ziplist = json_result.info['zipcode_by_city'][0];
                            for (var key in ziplist)
                            {
                                var value = ziplist[key];
                                var zipcodes = '<span id="zip-'+value+'" class="hover-underline-animation" onclick="updatezipcode(\''+value+'\',\''+$eid+'\')">'+value+'</span>'
                                avaiable_zipcodes = avaiable_zipcodes + zipcodes + ", ";
                            }
                            document.getElementById('Shippostcode_validate').style.display = 'block';
                            if (avaiable_zipcodes != "") {
                                document.getElementById('Shippostcode_validate').innerHTML = "Please pick listed your zipcode.<br>" + avaiable_zipcodes +" <span> <small style='color:gray;'>your zipcode not listed then please contact salesperson.</small></span>";
                            }

                        }else if(json_result.info.hasOwnProperty('city_by_state') && json_result.info['city_by_state'].length > 0)
                        {
                            var elmid_city = "updatezipcode(this.options[this.selectedIndex].value, 'Shipcity')";
                            document.getElementById('Shipcity').value = '';
                            var avaiable_city, optionlist = '';
                            var optionselect = '<select tabindex="8" id="Shipcity_validate_select" class="input-field" placeholder="Enter state/province..." ><option>Please select city</option>';
                            var citylist = json_result.info['city_by_state'][0];
                            for (var key in citylist)
                            {
                                var value = citylist[key];
                                optionlist  = optionlist + '<option value="'+value+'">'+value+'</option>';
                            }
                            if (optionlist != "") {
                                document.getElementById('Shipcity_validate').style.display ='block';
                                document.getElementById('Shipcity').style.display ='none';
                                document.getElementById('Shipcity-valid').style.display = 'none';
                                document.getElementById('Shipcity_validate').innerHTML = optionselect + optionlist + "</select><div class=\"input-validation\"></div>";
                                $('#Shipcity_validate_select').attr( 'onchange', elmid_city );
                            }else{
                                document.getElementById('Shipcity').style.display = 'block';
                                document.getElementById('Shipcity-valid').style.display = 'block';
                            }
                        }
                    } else
                    {
                        document.getElementById('Shippostcode_validate').innerHTML = '';
                        if (json_result.info["0"]) {
                            json_result.info[0] = json_result.info["0"];
                        }
                        tax_class = parseFloat(json_result.info[0]['total_sales_tax']);
                        console.log("tax_class:" + tax_class);
                        // console.log("tax_class_status:");
                        document.getElementById('taxclass').value = tax_class;
                        caltotal();
                    }
                }
            });
        }else{
            console.log("required data not avaiable");
        }
    }
    fetchavatax('Shippostcode');

    function updatezipcode(elmvalue, elmid){
        document.getElementById(elmid).value = elmvalue.trim();
        document.getElementById(elmid).style.display = "block";
        if (document.getElementById(elmid+"-valid")) {
            document.getElementById(elmid+"-valid").style.display = "block";
        }
        var shivalid = document.getElementById(elmid+'_validate');
        shivalid.innerHTML = '';
        shivalid.style.display = "none";
        fetchavatax ('Shippostcode');
    }


    var autocomplete = {};
    var autocompletesWraps = ['billing', 'shipping'];
    var billing_form = {
        street_number: 'short_name',
        route: 'long_name',
        locality: 'long_name',
        administrative_area_level_1: 'long_name',
        country: 'long_name',
        postal_code: 'short_name'
    };
    var shipping_form = {
        street_number: 'short_name',
        route: 'long_name',
        locality: 'long_name',
        administrative_area_level_1: 'long_name',
        country: 'long_name',
        postal_code: 'short_name'
    };

    // To Disable Console On Production
    // console.log = function() {}

    function initialize() {
        $.each(autocompletesWraps, function(index, name) {
            if ($('#' + name).length == 0) {
                return;
            }
            autocomplete[name] = new google.maps.places.Autocomplete($('#' + name + ' .autocomplete')[0], {
                types: ['geocode']
            });
            google.maps.event.addListener(autocomplete[name], 'place_changed', function() {
                var place = autocomplete[name].getPlace();
                var form = eval(name + '_form');
                for (var component in form) {
                    $('#' + name + ' .' + component).val('');
                    $('#' + name + ' .' + component).attr('disabled', false);
                }

                var province_value = "";
                for (var i = 0; i < place.address_components.length; i++) {
                    var addressType = place.address_components[i].types[0];

                    if (addressType != "undefined" && addressType !=
                        "administrative_area_level_2" && addressType !=
                        "sublocality_level_1") {
                        $("." + addressType).css("border-bottom-color", "");
                    }

                    if (addressType == "administrative_area_level_1") {
                        province_value = place.address_components[i][form[addressType]].toString();
                        // console.log("province_value:"+province_value);
                    }
                    if (addressType == "route" && name == "billing") {
                        var routval = place.address_components[i][form[addressType]];
                        document.getElementById('address1').value = document.getElementById('address1').value + " " + routval;
                    }

                    if (addressType == "route" && name == "shipping") {
                        var routval = place.address_components[i][form[addressType]];
                        document.getElementById('Shipaddress1').value = document.getElementById('Shipaddress1').value + " " + routval;
                        var taxcalinit = 1;
                    }

                    if (addressType == "country" && name == "billing") {
                        var country = place.address_components[i][form[addressType]];
                        document.getElementById("Shipcountry").value = country;
                        show_province(country, "state", province_value);
                    }
                    if (addressType == "country" && name == "shipping") {
                        var country = place.address_components[i][form[addressType]];
                        document.getElementById("Shipcountry").value = country;
                        show_province(country, "Shipstate", province_value);
                        var taxcalinit = 1;
                    }
                    if (typeof form[addressType] !== 'undefined') {
                        var val = place.address_components[i][form[addressType]];
                        $('#' + name + ' .' + addressType).val(val);
                    }
                }
                if (taxcalinit) {
                    fetchavatax('Shippostcode');
                }
                SetBilling();
            });
        });
    }
</script>
<!-- <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDHwQphKEKmoPi1TTPSPM6r_a26sjqoh58&libraries=places" async defer></script> -->
<script src="https://cdn.pagesense.io/js/bestaccessdoors/5c56b870ba814e6da1c0974ec83157d7.js"></script>
</body>

</html>

<style>
    .StripeElement {
        box-sizing: border-box;
        height: 40px;
        padding: 10px 12px;
        border: 1px solid transparent;
        border-radius: 4px;
        background-color: white;
        box-shadow: 0 1px 3px 0 #e6ebf1;
        -webkit-transition: box-shadow 150ms ease;
        transition: box-shadow 150ms ease;
    }

    .StripeElement--focus {
        box-shadow: 0 1px 3px 0 #cfd7df;
    }

    .StripeElement--invalid {
        border-color: #fa755a;
    }

    .StripeElement--webkit-autofill {
        background-color: #fefde5 !important;
    }
</style>

<?php ?>
