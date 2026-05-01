@extends('layouts.default-new')

@section('title', 'Blog')


@if (isset($post))
    @push('meta-title')
        <meta name="title" content="{{$post->title}}">
    @endpush

    @if ($post->image)
        @push('meta-image')
            <meta name="image" content="{{ asset('/images/posts/' . $post->image) }}">
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
    <div class="container p-5 bg-[#fbfaf7]">
        <div class="text-3xl uppercase">ARTICLES</div>
        <hr>
        <div class="pt-4">
            @foreach ($posts as $post)
            <article class="border rounded-3xl bg-white/80 shadow-xl">
                <header>
                    <div class="pl-6 pt-3"><a class="font-bold dark:text-yellow-500 flex items-center text-xl" href="blogs/{{$post->slug}}">{{ $post->title }}</a></div>
                </header>
                <p>{{ $post->subtitle }}</p>
                <section>
                    @if (!empty($post->image))
                    <!-- Grid with image -->
                    <div class="grid grid-cols-4 gap-4 p-4 items-center">
                        <!-- Column 1: Image -->
                        <a href="blogs/{{$post->slug}}">
                            <img alt="{{ $post->title }}" class="w-[200px]" src="/images/posts/{{ $post->image }}">
                        </a>

                        <!-- Column 2: Content -->
                        <div>
                            @if (strlen($post->post) > 300)
                                <p class="pt-2">{!! strip_tags(substr($post->post,0,300)) !!} ... </p>

                            @else
                                <p class="pt-2">{!! $post->post !!}</p>
                            @endif
                        </div>

                        <!-- Column 3: Date -->
                        <div class="w-[120px] text-right">
                            {{ $post->created_at->format('M j, Y') }}
                        </div>

                        <div class="flex border-t border-[#ece2d6] pt-5 md:justify-start lg:justify-end lg:border-t-0 lg:pt-0">
                            <a href="blogs/{{$post->slug}}" class="inline-flex items-center justify-center gap-4 rounded-full border border-[#caa96a] px-7 py-4 text-[1.08rem] text-[#8a6e3d] transition hover:bg-[#faf3e7]">
                                <span>Read article</span>
                                <span aria-hidden="true">›</span>
                            </a>
                        </div>
                    </div>
                    @else
                    <!-- Grid without image -->
                    <div class="grid grid-cols-3 gap-4 p-4 items-center">
                        <!-- Column 1: Content spans first column -->
                        <div>
                            @if (strlen($post->post) > 300)
                                <h2><p class="pt-2">{!! strip_tags(substr($post->post,0,300)) !!} ... </p></h2>
                            @else
                                <p class="pt-2">{!! $post->post !!}</p>
                            @endif
                        </div>

                        <!-- Column 2: Date -->
                        <div class="w-[120px] text-right">
                            {{ $post->created_at->format('M j, Y') }}
                        </div>

                        <div class="flex border-t border-[#ece2d6] pt-5 md:justify-start lg:justify-end lg:border-t-0 lg:pt-0">
                            <a href="blogs/{{$post->slug}}" class="inline-flex items-center justify-center gap-4 rounded-full border border-[#caa96a] px-7 py-4 text-[1.08rem] text-[#8a6e3d] transition hover:bg-[#faf3e7]">
                                <span>Read article</span>
                                <span aria-hidden="true">›</span>
                            </a>
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
            <img alt="{{ $post->title }}" src="/images/posts/{{ $post->image }}">
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