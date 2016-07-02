<html>
<head>
    <title>&commat;{{ $botName }} Quotes</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <style>
        table tr:target {
            background-color: lightgreen;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="text-center">
        <h1><a href="https://telegram.me/{{ $botName }}">&commat;{{ $botName }}</a> Quotes<small>Disclaimer: The quotes posted here are not necessarily representative of the views of the people saying them. Only a fool would take anything posted here as fact.</small></h1>
        <h5>View any of these quotes along with extra quote info in Telegram by messaging <a href="https://telegram.me/{{ $botName }}">&commat;{{ $botName }}</a> with the command <code>/q [quote id]</code></h5>
    </div>
    @if (count($quotes))
        <div class="text-center">
            {!! $quotes->render() !!}
        </div>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>ID</th>
                <th>From</th>
                <th>Content</th>
                <th>Date</th>
                <th>Comment</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($quotes as $quote)
                <tr id="{{ $quote->id }}">
                    <?php
                    $quotee = \Asuka\Http\AsukaDB::getUser($quote->user_id);
                    $citation = $quotee->first_name;
                    if ($quotee->last_name) {
                        $citation .= sprintf(' %s', $quotee->last_name);
                    }

                    if ($quotee->username) {
                        $citation .= sprintf(' (@%s)', $quotee->username);
                    }
                    ?>
                    <td><a href="#{{ $quote->id }}">{{ $quote->id }}</a></td>
                    <td>{{ $citation }}</td>
                    <td>{{ $quote->content }}</td>
                    <td>{{ date('D, jS M Y H:i:s T', $quote->message_timestamp) }}</td>
                    <td>{{ $quote->comment ?: 'N/A' }}</td>
                    @endforeach
                </tr>
        </table>
        <hr>

        <div class="text-center">
            {!! $quotes->render() !!}
        </div>
    @else
        <hr>
        <div class="message-area">
            <div class="alert alert-danger">No quotes found...</div>
        </div>
    @endif
</div>
</body>
</html>