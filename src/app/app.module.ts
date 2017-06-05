import { BrowserModule } from '@angular/platform-browser';
import { HttpModule } from '@angular/http';
import { NgModule, ErrorHandler } from '@angular/core';
import { IonicApp, IonicModule, IonicErrorHandler } from 'ionic-angular';
import { MyApp } from './app.component';

import { ShowCardPage } from '../pages/card/show';
import { NewCardPage } from '../pages/card/new';
import { DiscoverPage } from '../pages/discover/discover';
import { ProfilePage } from '../pages/profile/profile';
import { CommentComponent } from '../components/card/comment.component';
import { NewCommentComponent } from '../components/card/new-comment.component';
import { WallComponent } from '../components/card/wall.component';

import { StatusBar } from '@ionic-native/status-bar';
import { SplashScreen } from '@ionic-native/splash-screen';

@NgModule({
  declarations: [
    MyApp,
    DiscoverPage,
    ProfilePage,
    ShowCardPage,
    NewCardPage,
    CommentComponent,
    NewCommentComponent,
    WallComponent,
  ],
  imports: [
    BrowserModule,
    HttpModule,
    IonicModule.forRoot(MyApp),
  ],
  bootstrap: [IonicApp],
  entryComponents: [
    MyApp,
    DiscoverPage,
    ProfilePage,
    ShowCardPage,
    NewCardPage,
  ],
  providers: [
    StatusBar,
    SplashScreen,
    {provide: ErrorHandler, useClass: IonicErrorHandler}
  ]
})
export class AppModule {}
