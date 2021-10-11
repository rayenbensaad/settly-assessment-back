@component('mail::message')
# Verify Your Account


Thank you for creating an account with us. Don't forget to complete your registration!
    <br>
    Please click on the link below or copy it into the address bar of your browser to confirm your email address:

@component('mail::button', ['url' => 'http://localhost:3000/verifAccount/'.$verification_code  ])
Verify Account
@endcomponent

Thanks,<br>
@endcomponent


