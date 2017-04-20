{{--
    Copyright 2015-2017 ppy Pty. Ltd.

    This file is part of osu!web. osu!web is distributed with the hope of
    attracting more community contributions to the core ecosystem of osu!.

    osu!web is free software: you can redistribute it and/or modify
    it under the terms of the Affero GNU General Public License version 3
    as published by the Free Software Foundation.

    osu!web is distributed WITHOUT ANY WARRANTY; without even the implied
    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
    See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with osu!web.  If not, see <http://www.gnu.org/licenses/>.
--}}
@extends("master", [
    'title' => 'osu!',
    'blank' => 'true',
    'body_additional_classes' => 'osu-layout--body-dark'
])

@section("content")
    <nav class="osu-layout__row">
        <!-- Mobile Navigation -->
        @include('layout._header_mobile')

        <!-- Desktop Navigation -->
        <div class="landing-nav hidden-xs">
            <div class="landing-nav__section">
                @foreach (nav_links() as $section => $links)
                    <a
                        href="{{ array_values($links)[0] }}"
                        class="landing-nav__link {{ ($section == "home") ? "landing-nav__link--bold" : "" }}"
                    >
                        {{ trans("layout.menu.$section._") }}
                    </a>
                @endforeach

                <div class="landing-nav__locale-menu-link">
                    <span class="landing-nav__link js-menu" data-menu-target="landing--locale">
                        <img
                            class="landing-nav__locale-flag"
                            src="{{ flag_path(locale_flag(App::getLocale())) }}"
                            alt="{{ App::getLocale() }}"
                        >
                        {{ App::getLocale() }}
                    </span>

                    <div
                        class="js-menu landing-nav__locale-menu"
                        data-menu-id="landing--locale"
                        data-visibility="hidden"
                    >
                        @foreach (config('app.available_locales') as $locale)
                            <a
                                class="landing-nav__link landing-nav__link--locale"
                                href="{{ route('set-locale', ['locale' => $locale]) }}"
                                data-remote="1"
                                data-method="POST"
                            >
                                <span class="landing-nav__locale-link-pointer">
                                    <span class="fa fa-chevron-right"></span>
                                </span>

                                <img
                                    class="landing-nav__locale-flag"
                                    src="{{ flag_path(locale_flag($locale)) }}"
                                    alt="{{ $locale }}"
                                >

                                {{ $locale }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="landing-nav__section">
                <a
                    href="#"
                    class="landing-nav__link js-nav-toggle"
                    data-nav-mode="user"
                    data-nav-sub-mode="login"
                    title="{{ trans("users.anonymous.login_link") }}"
                >
                    {{ trans("users.login._") }}
                </a>

                <a
                    href="{{ route("users.register") }}"
                    class="landing-nav__link js-nav-toggle"
                    data-nav-mode="user"
                    data-nav-sub-mode="signup"
                >
                    {{ trans("users.signup._") }}
                </a>
            </div>
        </div>

    </nav>

    <div class="js-nav-data" id="nav-data-landing" data-turbolinks-permanent></div>
    @include('layout._popup', ['navPopupExtraClasses' => 'osu-layout__row--landing'])

    <div class="osu-page">
        <div class="landing-hero">
            <div class="landing-hero__bg-container">
                <div
                    class="landing-hero__bg-inner-container embed-responsive-16by9 js-yt-loop"
                    data-yt-loop-video-id="{{ config('osu.landing.video_id') }}"
                    data-yt-loop-class="landing-hero__bg"
                ></div>
            </div>

            <div class="landing-hero__pippi">
                <div class="landing-hero__pippi-logo"></div>
            </div>

            <div class="landing-hero__info">
                {!! trans("home.landing.players", ['count' => number_format($stats->totalUsers)]) !!},
                {!! trans("home.landing.online", [
                    'players' => number_format($stats->currentOnline),
                    'games' => number_format($stats->currentGames)]
                ) !!}
            </div>

            <div class="landing-hero__messages">
                <div class="landing-hero__slogan">
                    <h1 class="landing-hero__slogan-main">
                        {{ trans('home.landing.slogan.main') }}
                    </h1>

                    <h2 class="landing-hero__slogan-sub">
                        {{ trans('home.landing.slogan.sub') }}
                    </h2>
                </div>

                <div class="landing-hero__download">
                    <a href="{{ config('osu.urls.installer') }}" class="btn-osu-big btn-osu-big--download">
                        <span class="btn-osu-big__content">
                            <span class="btn-osu-big__left">
                                <span class="btn-osu-big__text-top">
                                    {{ trans("home.landing.download._") }}
                                </span>

                                <span class="btn-osu-big__text-bottom">{{ trans('home.landing.download.for', ['os' => 'Windows'])}}</span>
                            </span>

                            <span class="btn-osu-big__icon">
                                <span class="fa fa-cloud-download"></span>
                            </span>
                        </span>
                    </a>

                    <span class="landing-hero__download-other">{{ trans('home.landing.download.soon') }}</span>
                </div>
            </div>

            <div class="landing-hero__graph js-landing-graph"></div>

            <script id="json-stats" type="application/json">
                {!! json_encode($stats->graphData) !!}
            </script>
        </div>
    </div>

    <div class="osu-page">
        <div class="landing-middle-buttons">
            <a
                href="{{ route('support-the-game') }}"
                class="landing-middle-buttons__button landing-middle-buttons__button--support"
            ></a>

            <a
                href="{{ action('StoreController@getListing') }}"
                class="landing-middle-buttons__button landing-middle-buttons__button--store"
            ></a>

            <a
                href="https://blog.ppy.sh/"
                class="landing-middle-buttons__button landing-middle-buttons__button--blog"
            ></a>
        </div>
    </div>

    <footer class="osu-layout__section osu-layout__section--landing-footer">
        <div class="osu-layout__row osu-layout__row--landing-sitemap landing-sitemap">
            <div class="osu-layout__col-container osu-layout__col-container--landing-sitemap">
                @foreach (footer_links() as $section => $links)
                    <div class="osu-layout__col osu-layout__col--sm-3">
                        <ul class="landing-sitemap__list">
                            <li class="landing-sitemap__item">
                                <div class="landing-sitemap__header">{{ trans("layout.footer.$section._") }}</div>
                            </li>
                            @foreach ($links as $action => $link)
                                <li class="landing-sitemap__item"><a href="{{ $link }}" class="landing-sitemap__link">{{ trans("layout.footer.$section.$action") }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="landing-footer-social">
            <a href="{{ route('support-the-game') }}" class="landing-footer-social__icon landing-footer-social__icon--support">
                <span class="fa fa-heart"></span>
            </a>
            <a href="{{ osu_url("social.twitter") }}" class="landing-footer-social__icon landing-footer-social__icon--twitter">
                <span class="fa fa-twitter"></span>
            </a>
            <a href="{{ osu_url("social.facebook") }}" class="landing-footer-social__icon landing-footer-social__icon--facebook">
                <span class="fa fa-facebook-official"></span>
            </a>
        </div>

        <div class="landing-footer-bottom">
            <a href="{{ osu_url('legal.tos') }}" class="landing-footer-bottom__link">terms of service</a>
            <a href="{{ osu_url('legal.dmca') }}" class="landing-footer-bottom__link">copyright (DMCA)</a>
            <a href="{{ osu_url('legal.server') }}" class="landing-footer-bottom__link">server status</a>
            <a href="{{ osu_url('legal.osustatus') }}" class="landing-footer-bottom__link landing-footer-bottom__link--no-pad">@osustatus</a>

            <div class="landing-footer-bottom__copyright">ppy powered 2007-2017</div>
        </div>
    </footer>

    @include('layout.popup-container')
@endsection

@section ("script")
    @parent
@endsection
