<html>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.6;">
    <h2 style="margin-bottom: 12px;">New Customer Chat Email Lead</h2>

    <p style="margin: 0 0 12px;">
        A customer requested follow-up from the website chat while no specialist was available.
    </p>

    <table cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 16px;">
        <tr>
            <td style="padding: 4px 12px 4px 0; font-weight: bold;">Visitor:</td>
            <td style="padding: 4px 0;">{{ $chat['visitor_name'] ?: 'Unknown visitor' }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 12px 4px 0; font-weight: bold;">Email:</td>
            <td style="padding: 4px 0;">{{ $chat['visitor_email'] ?: 'Not provided' }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 12px 4px 0; font-weight: bold;">Page:</td>
            <td style="padding: 4px 0;">{{ data_get($page_context, 'page_path', 'Unknown page') }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 12px 4px 0; font-weight: bold;">Item:</td>
            <td style="padding: 4px 0;">
                @if(data_get($page_context, 'product.title'))
                    {{ data_get($page_context, 'product.title') }}
                    @if(data_get($page_context, 'product.id'))
                        (#{{ data_get($page_context, 'product.id') }})
                    @endif
                @elseif(data_get($page_context, 'product.id'))
                    Product #{{ data_get($page_context, 'product.id') }}
                @else
                    Not specified
                @endif
            </td>
        </tr>
    </table>

    <p style="margin: 0 0 8px; font-weight: bold;">Latest Message</p>
    <div style="padding: 12px 14px; border: 1px solid #e5e7eb; border-radius: 12px; background: #f9fafb; margin-bottom: 18px;">
        {{ $chat['last_message_preview'] ?: 'No message provided.' }}
    </div>

    @if(!empty($show_chat_url) && !empty($chat_url))
        <p style="margin: 0;">
            Open the chat inbox:
            <a href="{{ $chat_url }}" style="color: #991b1b;">{{ $chat_url }}</a>
        </p>
    @endif
</body>
</html>
