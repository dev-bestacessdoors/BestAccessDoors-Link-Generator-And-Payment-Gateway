<?php
function setapikey(data){
  echo "working ". data;
};
?>

<script>
var testingData = 'code1';

var stripe = Stripe('pk_test_THcb5wa7FNLaFSjHwzVAXVhu00DbXkIZw9');
var elements = stripe.elements();

var style = {
  base: {
    // Add your base input styles here. For example:
    fontSize: '16px',
    color: '#32325d',
  },
};

var card = elements.create('card', {style: style});
card.mount('#card-element');
</script>
