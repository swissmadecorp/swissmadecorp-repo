@extends('layouts.default-new')

@section('title', 'Blog')

@section ('header') 
<style>
    article a {color: #0095ff}
</style>
<link href="{{ asset('/lightgallery/css/lightgallery.css') }}" rel="stylesheet">    
@endsection

@section ('content')

@if (isset($posts))


<div class="flex justify-center">
    <div class="container p-5">
        <h1 class="text-3xl uppercase">ARTICLES</h4>
        <hr>
        <div class="pt-4">
            @foreach ($posts as $post)
            <article>
                <section>
                    @if (!empty($post->image ))
                    <div class="grid grid-cols-[1fr_3fr] p-4">
                    @else
                    <div class="pl-4 pr-4">
                    @endif
                        @if (isset($post->image ))
                        <a href="blogs/{{$post->slug}}"><img alt="{{ $post->title }}" class="w-[200px]" src="/images/posts/thumbs/{{ $post->image }}"></a>
                        @endif
                        
                        <div>
                            <header>
                                <a class="text-yellow-500 dark:text-yellow-500 font-medium flex items-center text-xl" href="blogs/{{$post->slug}}">{{ $post->title }}</a>
                            </header>
                            <p>{{ $post->subtitle }}</p>
                            
                            @if (strlen($post->post) > 500)
                                <p class="pt-2">{{ strip_tags(substr($post->post,0,500)) }} ... </p> <div class="more"><br><a class="text-gray-500 dark:text-gray-500 font-medium flex items-center" href="blogs/{{$post->slug}}"> Read More &raquo;</a></div>
                            @else 
                                <p class="pt-2">{!! $post->post !!}</p>
                            @endif
                        </div>
                    </div>
                </section>
            </article>
            <hr>
            <div class="sub-text-line m_bottom_10"><img src="/images/title.png"></div>
            @endforeach
        </div>

        @include('pagination', ['paginator' => $posts])
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
                        <h1 class="text-yellow-500 dark:text-yellow-500 font-medium flex items-center text-xl" href="blogs/{{$post->slug}}">{{ $post->title }}</h1>
                    </header>
                    <p class="pb-4 font-bold">{{ $post->subtitle }}</p>
                    
                    {!! $post->post !!}
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