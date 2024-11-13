<p>Dear {{ $receiverAccount->user->name }},</p>
<p>You have received {{ $amount }} in your account.</p>
<p>Amount added: {{ $amount }}</p>
<p>Your new balance: {{ $receiverAccount->balance }}</p>
