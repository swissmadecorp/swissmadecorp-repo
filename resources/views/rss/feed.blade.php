<?=
'<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL
?>
<rss version="2.0">
    <channel>
        <title><![CDATA[ Swiss Made Corp ]]></title>
        <link><![CDATA[ https://swissmadecorp.com/feed ]]></link>
        <description><![CDATA[ Brand new, pre-owned, luxury, casual, and dress watches for men and women - Swiss Made Corp... ]]></description>
        <language>en</language>
        <pubDate>{{ now() }}</pubDate>

        @foreach($products as $product)
            @if (isset($product->images[0])) {
            
            <item>
                <title><![CDATA[{{ $product->title }}]]></title>
                <image><![CDATA[{{ 'https://swissmadecorp.com/images/'.$product->images[0]->location; }}]]></image>
                <link>https://swissmadecorp.com/{{ $product->slug }}</link>
                <description><![CDATA[{!! $product->title !!}]]></description>
                <category>{{ $product->categories->category_name }}</category>
                <guid>{{ $product->id }}</guid>
            </item>
            @endif
        @endforeach
    </channel>
</rss>