<template>
    <div>
        <!-- Controls -->
        <div v-if="data.tracks.length > 1" class="controls" :class="stick ? 'hide' : ''">

            <!-- Mobile Controls -->
            <div class="mobile-controls text-left text-sm-center">
                <div class="active-track" @click="toggleTrackDrop">
                    <span class="prompt uppercase">Select Category: </span>
                    <h4 class="uppercase">{{ data.tracks[activeIndex].name }}</h4>
                    <span class="arrow">›</span>
                </div>
                <transition name="drop">
                    <ul v-show="dropOpen" class="track-drop not-a-list text-center">
                        <li v-for="(track, index) in data.tracks" @click="selectTrack(index)">
                            <h4 class="uppercase" v-text="track.name"></h4>
                        </li>
                    </ul>
                </transition>
            </div>
        </div>

        <!-- Sticky Controls -->
        <div v-if="data.tracks.length > 1 && stick" class="controls stick">

            <!-- Mobile Controls -->
            <div class="mobile-controls text-left text-sm-center">
                <div class="active-track" @click="toggleTrackDrop">
                    <span class="prompt uppercase">Select Track: </span>
                    <h4 class="uppercase">{{ data.tracks[activeIndex].name }}</h4>
                    <span class="arrow">›</span>
                </div>
                <transition name="drop">
                    <ul v-show="dropOpen" class="track-drop not-a-list text-center">
                        <li v-for="(track, index) in data.tracks" @click="selectTrack(index)">
                            <h4 class="uppercase"><a :href="'#' + track.route" v-text="track.name"></a></h4>
                        </li>
                    </ul>
                </transition>
            </div>
        </div>

        <!-- Active Track -->
        <div :id="track.route" v-for="(track, index) in data.tracks" v-show="index === activeIndex">
            <section class="bg-white">
                <div class="content-container">
                    <!-- Category Overview -->
                    <div class="content-max text-center padding overview">
                        <copy-pod
                            class="pad-bottom"
                            :title-sm="track.name"></copy-pod>
                        <!--<p v-text="track.overview"></p>-->
                    </div>

                    <!-- Category Filters -->
                    <div v-if="track.tabs" class="_detail-nav">
                        <div v-for="(tab, i) in track.tabs" @click="selectSubTrack(i)" class="_detail-nav-item" :class="[i === activeSubIndex ? '_active' : '']" :data-index="i">{{ tab.name }}</div>
                    </div>
                </div>
            </section>
            <section class="bg-light padding-top">
                <div class="content-container">
                    <div v-if="track.cards.length > 1 && track.cards.length <= 2">
                        <!-- 2 cards -->
                        <row class="col-md-1 align-center row-2">
                            <column>
                                <iframe v-bind:class="track.route + '_0'" class="_embed-card" marginheight="0" marginwidth="0" frameborder="0" scrolling="no"></iframe>
                            </column>
                        </row>
                        <row class="col-md-1 align-center row-2 m-bottom">
                            <column>
                                <iframe v-bind:class="track.route + '_1'" class="_embed-card" marginheight="0" marginwidth="0" frameborder="0" scrolling="no"></iframe>
                            </column>
                        </row>
                    </div>
                    <div v-else-if="track.cards.length > 1 && track.cards.length <= 3">
                        <!-- 3 cards -->
                        <row id="3-cards" class="col-md-2 align-center margin-bottom">
                            <column>
                                <iframe v-bind:class="track.route + '_0'" class="_embed-card tall"  marginheight="0" marginwidth="0" frameborder="0" scrolling="no"></iframe>
                            </column>
                            <column class="padding-left">
                                <row class="col-md-1 align-center">
                                    <column>
                                        <iframe v-bind:class="track.route + '_1'" class="_embed-card" style="margin-bottom: 35px !important"  marginheight="0" marginwidth="0" frameborder="0" scrolling="no"></iframe>
                                    </column>
                                </row>
                                <row class="col-md-1 align-center">
                                    <column>
                                        <iframe v-bind:class="track.route + '_2'" class="_embed-card" marginheight="0" marginwidth="0" frameborder="0" scrolling="no"></iframe>
                                    </column>
                                </row>
                            </column>
                        </row>
                    </div>
                    <div v-else>
                        <!-- 4 cards -->
                        <row id="4-cards" class="col-md-2 align-center row-4">
                            <column class="padding-right pd-bottom">
                                <iframe class="_embed-card" v-bind:class="track.route + '_0'" marginheight="0" marginwidth="0" frameborder="0" scrolling="no"></iframe>
                            </column>
                            <column>
                                <iframe class="_embed-card" v-bind:class="track.route + '_1'" marginheight="0" marginwidth="0" frameborder="0" scrolling="no"></iframe>
                            </column>
                        </row>
                        <row class="col-md-2 align-center margin-bottom">
                            <column class="padding-right pd-bottom">
                                <iframe class="_embed-card" v-bind:class="track.route + '_2'" marginheight="0" marginwidth="0" frameborder="0" scrolling="no"></iframe>
                            </column>
                            <column>
                                <iframe class="_embed-card" v-bind:class="track.route + '_3'" marginheight="0" marginwidth="0" frameborder="0" scrolling="no"></iframe>
                            </column>
                        </row>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>


<script type="text/babel">
    import CopyPod from '../../components/copy-pod.vue'
    import Row from '../../components/row.vue';
    import Column from '../../components/column.vue';
    import Quarterlyearnings from '../../data/company/quarterlyearnings.js';

    export default {
        components: {
            CopyPod,
            Row,
            Column
        },
        data () {
            let earningsData = Quarterlyearnings;

            return {
                data: earningsData,
                activeIndex: 0,
                activeSubIndex: 0,
                dropOpen: false,
                stick: false
            }
        },
        methods: {
            selectTrack (index) {
                this.activeIndex = index
                this.dropOpen = false
                this.initCards(index);
            },
            toggleTrackDrop () {
                if (!this.dropOpen) {
                    this.dropOpen = true
                } else {
                    this.dropOpen = false
                }
            },
            selectSubTrack (index) {
                this.activeSubIndex = index
                let cards = this.data.tracks[this.activeIndex].tabs[index].cards
                let iframe = '';

                for (var i in cards) {
                    iframe = this.getIframe(i);
                    iframe.src = cards[i]
                }
            },
            getIframe (index) {
                return document.getElementById(this.data.tracks[this.activeIndex].route).getElementsByClassName(this.data.tracks[this.activeIndex].route + '_' + index)[0];
            },
            initCards (index) {
                let cards = this.data.tracks[index].cards
                let iframe = '';

                for (var i in cards) {
                    iframe = this.getIframe(i);

                    if(!iframe.src) {
                        iframe.src = cards[i]
                    }
                }
            },
            handleScroll () {
                let stickyEl = document.getElementById('sticky-tracker')
                let stickyBox = stickyEl.getBoundingClientRect()
                let stickyBottom = (stickyBox.bottom - 60)
                if (stickyBottom <= 0) {
                    this.stick = true
                } else {
                    this.stick = false
                }
            }
        },
        mounted () {
            window.addEventListener('scroll', this.handleScroll);
            // only load card iframes for current category
            this.initCards(this.activeIndex);
        }
    }
</script>

<style lang="sass" scoped>
@import '../../../assets/sass/miyagi/_variables';

.padding-top {
    padding-top: 60px;
}
.controls {
    width: 100%;
    left: 0;
    color: $white;
    transition: background-color .4s ease 0s, opacity 0 ease 0;
    &.hide {
        opacity: 0;
    }
    &.stick {
        position: fixed;
        z-index: 2;
        top: $headerHeight;
    }
    @include bp-lg {
        box-shadow: 0 5px 12px 0 rgba(0, 0, 0, 0.4);
    }

    .mobile-controls {
        position: relative;
        left: 0;
        z-index: 2;
        width: 100%;
        height: $headerHeight - 10;
        white-space: nowrap;
        color: $white;
        @media only screen and (max-width: 515px) {
            height: 100px;
            text-align: center;
        }
        .active-track {
            background-color: #9CE;
            cursor: pointer;
            padding-left: $pad;
            position: relative;
            height: inherit;
            z-index: 2;
            box-shadow: 0 5px 12px 0 rgba(0, 0, 0, 0.35);
            transition: all .4s ease 0s;
            > span {
                vertical-align: middle;
                display: inline-block;
                line-height: $headerHeight - 10;
                font-weight: 700;
                font-size: 12px;
                &.prompt {
                    opacity: .5;
                }
                &.arrow {
                    width: 40px;
                    height: 50px;
                    transform: rotate(90deg);
                    font-weight: 300;
                    font-size: 40px;
                    line-height: 50px;
                    text-align: center;
                }
            }
            > h4 {
                vertical-align: middle;
                display: inline-block;
                position: relative;
                padding-left: $buff;
            }
        }
        .track-drop {
            position: absolute;
            top: $headerHeight - 10;
            width: 100%;
            box-shadow: 0 5px 12px 0 rgba(0, 0, 0, 0.4);
            transition: all .4s ease 0s;
            > li {
                background-color: #333;
                border-bottom: 1px solid #fff;
                cursor: pointer;
                height: 50px;
                > h4 {
                    line-height: 50px;
                    &:hover {
                        text-decoration: underline;
                    }
                    > a {
                        display: block;
                        line-height: 50px;
                    }
                }
            }
        }
    }
}
.not-a-list {
    list-style-type: none;
}
._detail-nav {
    text-align: left;
    border-bottom: 1px solid #e8e8e8;
}
._detail-nav-item {
        display: inline-block;
        padding: 8px 4px;
        margin-right: 15px;
        color: #888;
        text-transform: uppercase;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        user-select: none;
}
._detail-nav-item:last-child {
    margin-right: 0px;
}
._detail-nav-item._active {
        color: #555;
        border-bottom: 4px solid #9CE;
}
@media only screen and (min-width: 769px) {
    .padding-right {
        padding-right: 35px !important;
    }
    .padding-left {
        padding-left: 35px !important;
    }
}
._embed-card {
    display: block;
    width: 100%;
    height: 370px;
    margin: 0 auto 15px auto;
    box-shadow: 0 4px 15px rgba(0, 0, 0, .1);
    @media only screen and (min-width: 769px) {
        display: inline-block;
        vertical-align: top;
    }
    @media only screen and (max-width: 768px) {
        width: 90%;
    }
    &.tall {
        height: 775px;
        @media only screen and (max-width: 768px) {
            margin-bottom: 35px;
            height: 600px;
        }
    }
}
.row-4, .row-2 {
    margin-bottom: 25px;
}
.margin-bottom, .m-bottom {
    margin-bottom: 60px;
}
.overview h3 {
    font-size: 24px;
    font-weight: 600;
}
@media only screen and (max-width: 768px) {
    .overview {
        padding-left: 25px;
        padding-right: 25px;
    }
    .pd-bottom {
        padding-bottom: 20px !important;
    }
    .row-4 {
        margin-bottom: 20px;
    }
}
@media only screen and (max-width: 515px) {
    .controls .mobile-controls .active-track > span {
        display: block;
    }
    .controls .mobile-controls .active-track > span.arrow {
        display: inline-block;
    }
    .controls .mobile-controls .track-drop {
        top: 100px;
    }
}
</style>
<style>
    .overview h3 {
        font-size: 24px;
        font-weight: 600;
    }
</style>