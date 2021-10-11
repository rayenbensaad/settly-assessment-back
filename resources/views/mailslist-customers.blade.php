@component('mail::message')
# Hello,


List of Customers:

@component('mail::table')
| **Name**                                       | **Email**             |
| ---------------------------------------------- |:---------------------:| 
@foreach ($customers as $customer)
| {{$customer->name}} | <{{$customer->email}}> | 
@endforeach
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent