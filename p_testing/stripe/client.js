
var stripe = Stripe(testingData);
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


// var form = document.getElementById('payment-form');
// form.addEventListener('submit', function(event) {
// event.preventDefault();
// console.log("Form Submiteed called");
//
//
//   stripe.createToken(card).then(function(result) {
//     if (result.error) {
//       // Inform the customer that there was an error.
//       var errorElement = document.getElementById('card-errors');
//       errorElement.textContent = result.error.message;
//     } else {
//       stripeTokenHandler(result.token);
//       console.log("token"+stripeTokenHandler(result.token));
//     }
//   });
//
//
// });
//
// function stripeTokenHandler(token) {
//   // Insert the token ID into the form so it gets submitted to the server
//   var form = document.getElementById('payment-form');
//   var hiddenInput = document.createElement('input');
//   hiddenInput.setAttribute('type', 'hidden');
//   hiddenInput.setAttribute('name', 'stripeToken');
//   hiddenInput.setAttribute('value', token.id);
//   form.appendChild(hiddenInput);
//   // Submit the form
//   form.submit();
// }

//
// var form = document.getElementById('payment-form');
// var ownerInfo = {
//   owner: {
//     name: 'Jenny Rosen',
//     address: {
//       line1: 'Nollendorfstraße 27',
//       city: 'Berlin',
//       postal_code: '10777',
//       country: 'DE',
//     },
//     email: 'jenny.rosen@example.com'
//   },
// };
// form.addEventListener('submit', function(event) {
//   event.preventDefault();
//
//   stripe.createSource(card, ownerInfo).then(function(result) {
//     if (result.error) {
//       // Inform the user if there was an error
//       var errorElement = document.getElementById('card-errors');
//       errorElement.textContent = result.error.message;
//     } else {
//       // Send the source to your server
//       stripeSourceHandler(result.source);
//     }
//   });
// });
