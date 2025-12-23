<table>
    <tr>
    @if ($is_notes || $is_serial || $is_cost)
        <?php $colSpan = 5 ?>
    @endif

    @if ($is_serial && $is_cost)
        <?php $colSpan = 6 ?>
    @endif

    @if ($is_notes && $is_serial)
        <?php $colSpan = 6 ?>
    @endif

    @if ($is_notes && $is_serial && $is_cost)
        <?php $colSpan = 7 ?>
    @endif

    @if (!$is_notes && !$is_serial && !$is_cost)
        <?php $colSpan = 4 ?>
    @endif

        <td colspan="{{$colSpan}}" style="height: 50px;vertical-align: middle;font-size: 14px" vlign="middle">Swiss Made Corp. Proposal</td>
    </tr>
    <!-- Begin Header -->
    <tr>
        <th style="background-color: #e7e7e7; font-weight: bold;text-align: center">Image</th>
        <th style="background-color: #e7e7e7; font-weight: bold;text-align: center">Prod Id</th>
        <th style="background-color: #e7e7e7; font-weight: bold;text-align: center">Description</th>
        <th style="background-color: #e7e7e7; font-weight: bold;text-align: center">Qty</th>
        @if ($is_serial)
        <th style="background-color: #e7e7e7; font-weight: bold;text-align: center">Serial</th>
        @endif
        @if ($is_cost)
        <th style="background-color: #e7e7e7; font-weight: bold;text-align: center">Cost</th>
        @endif
        <th style="background-color: #e7e7e7; font-weight: bold;text-align: center">Retail</th>
        @if ($is_notes)
        <th style="background-color: #e7e7e7; font-weight: bold;text-align: center">Comments</th>
        @endif
        <!-- <th style="background-color: #e7e7e7; font-weight: bold;text-align: center">Retail</th> -->
    </tr>
    <!-- End Header -->

    <!-- Begin Body -->

    @foreach($products as $product)
        <?php
            $image=$product->images->first();
            $noImage=false;$path='';

            if ($image)
                $path = base_path().'/public/images/thumbs/'.$image->location;

            if (!file_exists($path)) {
                $noImage=true;
                $path = base_path().'/public/images/no-image.jpg';
            }
        ?>

        <tr>
            <td style="text-align: center; width: 97px; height: 112px;" valign="middle">
                <img src="{{ $path }}" height="95px">
            </td>
            <td style="width: 70px;vertical-align: middle;text-align: left">{{$product->id}}</td>
            <td style="width: 350px;word-wrap: break-word;vertical-align: middle" vlign='middle'>{{ $product->title }}</td>
            <td style="width: 70px;vertical-align: middle;text-align: center">{{ $product->p_qty }}</td>
            @if ($is_serial)
            <td style="width: 112px;vertical-align: middle;text-align: right">{{ $product->p_serial }}</td>
            @endif
            @if ($is_cost)
            <td style="width: 112px;vertical-align: middle;text-align: right">{{$product->p_price}}</td>
            @endif
            <td style="width: 112px;vertical-align: middle;text-align: right">{{number_format($product->p_retail, 0, '.', ',')}}</td>
            @if ($is_notes)
            <td style="width: 112px;vertical-align: middle;text-align: right">
                @if ($product->p_papers)
                    papers
                    @if ($product->p_condition == 1 || $product->p_condition == 4 || $product->p_condition == 5)
                    - {{Conditions()->get($product->p_condition)}}
                    @endif
                @else
                    @if ($product->p_condition == 1 || $product->p_condition == 4 || $product->p_condition == 5)
                    {{Conditions()->get($product->p_condition)}}
                    @endif
                @endif
            </td>
            @endif
            <!-- <td style="text-align: right;width: 75px">{{ $product->p_retail }}</td> -->
        </tr>
    @endforeach
    <!-- End Body -->

    <!-- Begin Footer -->
    <tr>
        <td style="background-color: #e7e7e7;font-weight: bold">Total:</td>
        <td style="background-color: #e7e7e7;text-align: right"></td>
        <td style="background-color: #e7e7e7;text-align: right"></td>
        <td style="background-color: #e7e7e7;text-align: center"></td>
        @if ($is_serial)
        <td style="background-color: #e7e7e7;text-align: right"></td>
        @endif
        @if ($is_cost)
        <td style="background-color: #e7e7e7;"></td>
        @endif
        <td style="background-color: #e7e7e7;text-align: center"></td>
        @if ($is_notes)
        <td style="background-color: #e7e7e7;"></td>
        @endif
    </tr>
    <!-- End Footer -->
</table>