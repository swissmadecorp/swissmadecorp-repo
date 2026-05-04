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

@php
    $estimateReadTime = function ($html) {
        $wordCount = str_word_count(trim(preg_replace('/\s+/', ' ', strip_tags($html ?? ''))));

        return max(3, (int) ceil($wordCount / 220));
    };
@endphp
<div class="flex justify-center">
    <div class="container p-5 bg-[#fbfaf7]">
        <div class="text-3xl uppercase">ARTICLES</div>
        <hr>
        <div class="pt-4">
            @foreach ($posts as $post)
            <article class="border rounded-3xl bg-white/80 shadow-xl">
                <header>
                    <div class="pl-4 pt-3">
                        <a class="font-bold dark:text-yellow-500 flex items-center text-xl" href="blogs/{{$post->slug}}">{{ $post->title }}</a>
                        <p>{{ $post->subtitle }}</p>
                    </div>

                </header>
                <section>
                    @if (!empty($post->image))
                    <!-- Grid with image -->
                    <div class="grid gap-4 p-1.5 md:grid-cols-[170px_minmax(0,1fr)_140px_180px] md:p-4 md:items-center">
                        <!-- Column 1: Image -->
                        <a href="blogs/{{$post->slug}}" class="block w-full overflow-hidden rounded-[18px] bg-[#efe6da] md:w-[170px]">
                            <div class="aspect-[4/3] w-full">
                                <img alt="{{ $post->title }}" class="h-full w-full object-cover object-center" src="/images/posts/{{ $post->image }}">
                            </div>
                        </a>

                        <!-- Column 2: Content -->
                        <div class="min-w-0">
                            @if (strlen($post->post) > 500)
                                <p class="pt-2">{!! strip_tags(substr($post->post,0,500)) !!} ... </p>

                            @else
                                <p class="pt-2">{!! $post->post !!}</p>
                            @endif
                        </div>

                        <!-- Column 3: Date -->
                        <div class="space-y-2">
                             <div class="flex gap-2">
                                <p>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-blue-600">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
                                    </svg>
                                </p>
                                <p>{{ $estimateReadTime($post->post) }} min read</p>
                            </div>
                            <div class="flex gap-2">
                                <p>
                                    <svg xmlns="http://w3.org" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-gray-600">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                    </svg>
                                </p>
                                <p>{{ $post->created_at->format('M j, Y') }}</p>
                            </div>

                        </div>

                         <!-- Column 4: Read more button -->
                        <div class="flex border-t border-[#ece2d6] pt-5 md:justify-start lg:justify-end lg:border-t-0 lg:pt-0">
                            <a href="blogs/{{$post->slug}}" class="inline-flex items-center justify-center gap-4 rounded-full border border-[#caa96a] p-4 text-[1.08rem] text-[#8a6e3d] transition hover:bg-[#faf3e7]">
                                <span>Read article</span>
                                <span aria-hidden="true">›</span>
                            </a>
                        </div>
                    </div>
                    @else
                    <!-- Grid without image -->
                    <div class="grid grid-cols-2 md:grid-cols-[2fr_1fr_1fr] gap-4 p-4 items-center">
                        <!-- Column 1: Content spans first column -->
                        <div>
                            @if (strlen($post->post) > 500)
                                <h2><p class="pt-2">{!! strip_tags(substr($post->post,0,500)) !!} ... </p></h2>
                            @else
                                <p class="pt-2">{!! $post->post !!}</p>
                            @endif
                        </div>

                        <!-- Column 2: Date -->
                        <div class="space-y-2">
                             <div class="flex gap-2">
                                <p>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-blue-600">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
                                    </svg>
                                </p>
                                <p>{{ $estimateReadTime($post->post) }} min read</p>
                            </div>
                            <div class="flex gap-2">
                                <p>
                                    <svg xmlns="http://w3.org" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-gray-600">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                    </svg>
                                </p>
                                <p>{{ $post->created_at->format('M j, Y') }}</p>
                            </div>

                        </div>

                        <div class="flex border-t border-[#ece2d6] pt-5 md:justify-start lg:justify-end lg:border-t-0 lg:pt-0">
                            <a href="blogs/{{$post->slug}}" class="inline-flex items-center justify-center gap-4 rounded-full border border-[#caa96a] p-3 md:p-4 text-[1.08rem] text-[#8a6e3d] transition hover:bg-[#faf3e7]">
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
                        <h1 class="bg-[#fbf8f2] border dark:text-yellow-500 flex font-bold justify-center p-2.5 rounded-2xl shadow-xl text-2xl text-center" href="blogs/{{$post->slug}}">{{ $post->title }}</h1>
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
