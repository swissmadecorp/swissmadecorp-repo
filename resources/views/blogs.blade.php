@extends('layouts.default-new')

@section('title', 'Blog')


@if (isset($post))
    @push('meta-title')
        <meta name="title" content="{{$post->title}}">
    @endpush

    @if ($post->image)
        @push('meta-image')
            <meta name="image" content="{{ asset('/images/posts/thumbs/' . $post->image) }}">
        @endpush
    @endif

    @if ($post->keywords)
        @push('meta-keywords')
            <meta name="keywords" content="{{$post->keywords}}">
        @endpush
    @endif

    @if ($post->meta)
        @push('meta-description')
            <meta name="description" content="{{$post->meta}}">
        @endpush
    @endif

@endif

@section ('header')
<link href="{{ asset('/lightgallery/css/lightgallery.css') }}" rel="stylesheet">
@endsection

@section ('content')

@if (isset($posts))


<div class="flex justify-center">
    <div class="container p-5">
        <div class="text-3xl uppercase">ARTICLES</div>
        <hr>
        <div class="pt-4">
            @foreach ($posts as $post)
            <article>
                <section>
                    @if (!empty($post->image))
                    <!-- Grid with image -->
                    <div class="grid grid-cols-[200px_1fr_120px] gap-4 p-4 items-start">
                        <!-- Column 1: Image -->
                        <a href="blogs/{{$post->slug}}">
                            <img alt="{{ $post->title }}" class="w-[200px]" src="/images/posts/thumbs/{{ $post->image }}">
                        </a>

                        <!-- Column 2: Content -->
                        <div>
                            <header>
                                <div><a class="font-bold dark:text-yellow-500 flex items-center text-xl" href="blogs/{{$post->slug}}">{{ $post->title }}</a></div>
                            </header>
                            <p>{{ $post->subtitle }}</p>

                            @if (strlen($post->post) > 500)
                                <p class="pt-2">{!! strip_tags(substr($post->post,0,500)) !!} ... </p>
                                <div class="more"><br>
                                    <a class="text-gray-500 hover:text-blue-500 dark:text-gray-500 flex items-center" href="blogs/{{$post->slug}}"> Read More &raquo;</a>
                                </div>
                            @else
                                <p class="pt-2">{!! $post->post !!}</p>
                            @endif
                        </div>

                        <!-- Column 3: Date -->
                        <div class="w-[120px] text-right">
                            {{ $post->created_at->format('M j, Y') }}
                        </div>
                    </div>
                    @else
                    <!-- Grid without image -->
                    <div class="grid grid-cols-4 gap-4 p-4 items-start">
                        <!-- Column 1: Content spans first column -->
                        <div>
                            <header>
                                <h1><a class="font-bold dark:text-yellow-500 flex items-center text-xl" href="blogs/{{$post->slug}}">{{ $post->title }}</a></h1>
                            </header>
                            <p>{{ $post->subtitle }}</p>
                        </div>

                        <div>
                            @if (strlen($post->post) > 500)
                                <h2><p class="pt-2">{!! strip_tags(substr($post->post,0,500)) !!} ... </p></h2>
                            @else
                                <p class="pt-2">{!! $post->post !!}</p>
                            @endif
                        </div>

                        <!-- Column 2: Date -->
                        <div class="w-[120px] text-right">
                            {{ $post->created_at->format('M j, Y') }}
                        </div>

                        <div class="more col-span-3 text-right">
                            <a class="text-gray-500 hover:text-blue-500 dark:text-gray-500 flex items-center" href="blogs/{{$post->slug}}"> Read More &raquo;</a>
                        </div>
                    @endif
                </section>
            </article>
            <hr>
            <div class="sub-text-line m_bottom_10"><img src="/images/title.png"></div>
            @endforeach
        </div>

        {{$posts->links() }} <!-- Pagination links -->
    </div>
</div>
@else

    @if (isset($post))
        <article class="flex justify-center pb-4">
            <div class="container p-5">
            @if (!empty($post->image ))
            <div class="grid grid-cols-[2fr_3fr] gap-8">
            @else
            <div>
            @endif

            @if (isset($post->image ))
            <img alt="{{ $post->title }}" src="/images/posts/thumbs/{{ $post->image }}">
            @endif

                <div class="content-page">
                    <header>
                        <h1 class="font-bold dark:text-yellow-500 text-2xl flex justify-center bg-gray-200 p-2.5 text-center" href="blogs/{{$post->slug}}">{{ $post->title }}</h1>
                    </header>
                    <p class="mt-3 text-center pb-4 text-xl font-bold">{{ $post->subtitle }}</p>

                    <div class="post-content pl-11 pr-11">
                        {!! $post->post !!}
                    </div>
                </div>
            </div>
            </div>
        </article>
    @else
        <article class="flex justify-center pt-4">
            <div class="container">
                <header class="flex justify-center text-3xl">
                    <h4 class="">Article not found.</h4>
                </header>

                <div class="container content-page" style="padding: 44px">
                    <div class="text-xl">
                        <h4>We're sorry but no article is found with this name.</h4><h4>Please check the url and try again.<h4>
                </div>
            </div>
        </article>
    @endif

@endif

@endsection