@extends('layouts.default')

@section('title', 'Blog')

@section ('header') 
<link href="{{ asset('/lightgallery/css/lightgallery.css') }}" rel="stylesheet">    
@endsection

@section ('content')

<div class="m_top_75"></div>

@if (isset($posts))

<h4 class="text-center"><b>ARTICLES</b></h4>

<div class="container">
    @foreach ($posts as $post)
    <article class="article-small-header m_bottom_10">
        <section>
            <div class="row">
                @if (isset($post->image ))
                <div class="col-sm-4">
                    <div class="article-image">
                    <a href="blog/{{$post->slug}}"><img alt="{{ $post->title }}" class="m_top_6" src="/images/posts/thumbs/{{ $post->image }}"></a>
                    </div>
                </div>
                <div class="col-sm-8">
                @else
                <div class="col-sm-12">
                @endif
                
                    <div class="content-page">
                        <header class="section-header">
                        <a href="blog/{{$post->slug}}"><b class="post-title">{{ $post->title }}</b></a>
                        </header>
                        <p>{{ $post->subtitle }}</p>
                        
                        @if (strlen($post->post) > 500)
                            {{ strip_tags(substr($post->post,0,500)) }} ... <div class="more"><br><a href="blog/{{$post->slug}}"> Read More &raquo;</a></div>
                        @else 
                            {!! $post->post !!}
                        @endif

                        
                    </div>
                </div>
            </div>
        </section>
    </article>
    <hr>
    <div class="sub-text-line m_bottom_10"><img src="/images/title.png"></div>
    @endforeach

    @include('pagination', ['paginator' => $posts])
</div>
@else

    @if (isset($post))
        <article class="article-small-header">
        

            <div class="container content-page" style="padding: 44px">
                <div class="row">
                    @if (isset($post->image ))
                    <div class="col-md-4">
                        <img style="max-width: 100%" alt="{{ $post->title }}" class="m_top_6" src="/images/posts/{{ $post->image }}">
                    </div>
                    <div class="col-md-8">
                    @else
                    <div class="col-md-12">
                    @endif
                    
                        <header class="section-header">
                            <h4 class="post-title">{{ $post->title }}</h4>
                            <h5>{{ $post->subtitle }}</h5>
                        </header>
                        {!! $post->post !!}
                    </div>
                </div>
        </article>
    @else
    <article class="article-small-header">
        <header class="section-header">
            <h4 class="text-center post-title">Article not found.</h4>
            
        </header>

            <div class="container content-page" style="padding: 44px">
                <div class="row">
                    @if (isset($post->image ))
                    <div class="col-md-4">
                        <img style="max-width: 100%" class="m_top_6" src="/images/no-image.jpg">
                    </div>
                    <div class="col-md-8">
                    @else
                    <div class="col-md-12">
                    @endif
                    
                        <h4>We're sorry but no article is found with this name.</h4><h4>Please check the url and try again.<h4>
                    </div>
                </div>
        </article>
    @endif

@endif

@endsection