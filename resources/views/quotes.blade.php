<html>
<head>
    <title>Telegram Quotes</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <div class="text-center">
        <h1>Telegram Quotes</h1>
    </div>
    <hr>
    @if (count($quotes))
        <div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>From</th>
                            <th>Content</th>
                            <th>Added By</th>
                            <th>Date</th>
                            <th>Comment</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($quotes as $quote)
                            <tr>
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
                                <td>{{ $quote->id }}</td>
                                <td>{{ $citation }}</td>
                                <td>{{ $quote->content }}</td>
                                <?php
                                $quoter = \Asuka\Http\AsukaDB::getUser($quote->added_by_id);
                                $citation = $quoter->first_name;
                                if ($quoter->last_name) {
                                    $citation .= sprintf(' %s', $quoter->last_name);
                                }

                                if ($quoter->username) {
                                    $citation .= sprintf(' (@%s)', $quoter->username);
                                }
                                ?>
                                <td>{{ $citation }}</td>
                                <td>{{ date('D, jS M Y H:i:s T', $quote->message_timestamp) }}</td>
                                <td>{{ $quote->comment ?: 'N/A' }}</td>
                                @endforeach
                            </tr>
                    </table>
                <hr>
            </div>
        </div>

        <div class="text-center">
            {!! $quotes->render() !!}
        </div>
    @else
        <div class="message-area">
            <div class="alert alert-danger">No quotes found...</div>
        </div>
    @endif
</div>
</body>
</html>