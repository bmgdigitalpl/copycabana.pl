@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ asset('images/logo.png') }}" class="logo" alt="{{ config('app.name') }}" width="140">
</a>
</td>
</tr>
<tr>
<td>
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="stripe" style="background-color: #00A0DF;"></td>
<td class="stripe" style="background-color: #D51A70;"></td>
<td class="stripe" style="background-color: #FFED00;"></td>
<td class="stripe" style="background-color: #333333;"></td>
</tr>
</table>
</td>
</tr>
