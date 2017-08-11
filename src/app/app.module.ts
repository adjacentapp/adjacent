import { BrowserModule } 						    from '@angular/platform-browser';
import { HttpModule, JsonpModule }	                from '@angular/http';
import { NgModule, ErrorHandler } 	                from '@angular/core';
import { MyApp } 								    from './app.component';
import { IonicApp, IonicModule, IonicErrorHandler }	from 'ionic-angular';
import { Push, PushObject, PushOptions }			from '@ionic-native/push';

import { StatusBar } 			from '@ionic-native/status-bar';
import { SplashScreen } 		from '@ionic-native/splash-screen';
import { GoogleAnalytics }	    from '@ionic-native/google-analytics';
import { SocialSharing }		from '@ionic-native/social-sharing';

// import { LoginPage } 		from '../pages/login/login';
import { ShowCardPage } 		from '../pages/card/show';
import { NewCardPage } 			from '../pages/card/new';
import { DiscoverPage } 		from '../pages/discover/discover';
import { ProfilePage } 			from '../pages/profile/profile';
import { EditProfilePage } 	    from '../pages/profile/edit';
import { BookmarksPage } 		from '../pages/bookmarks/bookmarks';
import { MessagesPage } 		from '../pages/messages/index';
import { ShowMessagePage }	    from '../pages/messages/show';
import { FoundedPage }          from '../pages/founded/founded';
import { FollowersPage }        from '../pages/followers/followers';
import { MissionPage } 		    from '../pages/mission/mission';

import { CardComponent } 				from '../components/card/card.component';
import { CommentComponent } 			from '../components/card/comment.component';
import { NewCommentComponent } 			from '../components/card/new-comment.component';
import { WallComponent } 				from '../components/card/wall.component';
import { NotificationCenterComponent }	from '../components/notification/notification-center.component';
import { NotificationComponent }		from '../components/notification/notification.component';
import { MessageComponent } 			from '../components/message/message';

import { CardProvider } 		from '../providers/card/card';
import { ProfileProvider } 		from '../providers/profile/profile';
import { WallProvider } 		from '../providers/wall/wall';
import { BookmarksProvider } 	from '../providers/bookmarks/bookmarks';
import { AuthProvider } 		from '../providers/auth/auth';
import { GlobalsProvider } 		from '../providers/globals/globals';
import { NotificationProvider }	from '../providers/notification/notification';
import { MessagesProvider } 	from '../providers/messages/messages';
import { BookmarksComponent }	from '../components/bookmarks/bookmarks';

@NgModule({
  declarations: [
    MyApp,
    // LoginPage,
    DiscoverPage,
    ProfilePage,
    EditProfilePage,
    BookmarksPage,
    ShowCardPage,
    NewCardPage,
    MessagesPage,
    ShowMessagePage,
    FoundedPage,
    FollowersPage,
    MissionPage,
    CardComponent,
    CommentComponent,
    NewCommentComponent,
    WallComponent,
    NotificationCenterComponent,
    NotificationComponent,
    MessageComponent,
    BookmarksComponent
  ],
  imports: [
    BrowserModule,
    HttpModule,
    JsonpModule,
    IonicModule.forRoot(MyApp, {}),
  ],
  bootstrap: [IonicApp],
  entryComponents: [
    MyApp,
    // LoginPage,
    DiscoverPage,
    ProfilePage,
    EditProfilePage,
    BookmarksPage,
    ShowCardPage,
    NewCardPage,
    MessagesPage,
    ShowMessagePage,
    FoundedPage,
    FollowersPage,
    MissionPage
  ],
  providers: [
    StatusBar,
    SplashScreen,
    GoogleAnalytics,
    SocialSharing,
    CardProvider,
    ProfileProvider,
    WallProvider,
    BookmarksProvider,
    AuthProvider,
    {provide: ErrorHandler, useClass: IonicErrorHandler},
    GlobalsProvider,
    NotificationProvider,
    MessagesProvider,
    Push
  ]
})
export class AppModule {}
