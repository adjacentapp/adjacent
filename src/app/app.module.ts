import { BrowserModule } from '@angular/platform-browser';
import { HttpModule, JsonpModule } from '@angular/http';
import { NgModule, ErrorHandler } from '@angular/core';
import { IonicApp, IonicModule, IonicErrorHandler } from 'ionic-angular';
import { MyApp } from './app.component';

import { ShowCardPage } from '../pages/card/show';
import { NewCardPage } from '../pages/card/new';
import { DiscoverPage } from '../pages/discover/discover';
import { ProfilePage } from '../pages/profile/profile';
import { BookmarksPage } from '../pages/bookmarks/bookmarks';
import { CardComponent } from '../components/card/card.component';
import { CommentComponent } from '../components/card/comment.component';
import { NewCommentComponent } from '../components/card/new-comment.component';
import { WallComponent } from '../components/card/wall.component';
import { CardService } from '../services/card.service';
import { ProfileService } from '../services/profile.service';
import { WallService } from '../services/wall.service';
import { BookmarksService } from '../services/bookmarks.service';

import { StatusBar } from '@ionic-native/status-bar';
import { SplashScreen } from '@ionic-native/splash-screen';
import { GoogleAnalytics } from '@ionic-native/google-analytics';
import { SocialSharing } from '@ionic-native/social-sharing';

@NgModule({
  declarations: [
    MyApp,
    DiscoverPage,
    ProfilePage,
    BookmarksPage,
    ShowCardPage,
    NewCardPage,
    CardComponent,
    CommentComponent,
    NewCommentComponent,
    WallComponent,
  ],
  imports: [
    BrowserModule,
    HttpModule,
    JsonpModule,
    IonicModule.forRoot(MyApp),
  ],
  bootstrap: [IonicApp],
  entryComponents: [
    MyApp,
    DiscoverPage,
    ProfilePage,
    BookmarksPage,
    ShowCardPage,
    NewCardPage,
  ],
  providers: [
    StatusBar,
    SplashScreen,
    GoogleAnalytics,
    SocialSharing,
    CardService,
    ProfileService,
    WallService,
    BookmarksService,
    {provide: ErrorHandler, useClass: IonicErrorHandler}
  ]
})
export class AppModule {}
