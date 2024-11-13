<p>Dear {{ $senderAccount->user->name }},</p>
<p>Your transaction of {{ $amount }} was successful.</p>
<p>Amount deducted: {{ $amount }}</p>
<p>Your new balance: {{ $senderAccount->balance }}</p>
