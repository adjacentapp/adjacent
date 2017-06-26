import { Component, ViewChild } from '@angular/core';

import { Platform, MenuController, Nav } from 'ionic-angular';

import { DiscoverPage } from '../pages/discover/discover';
import { ProfilePage } from '../pages/profile/profile';
import { NewCardPage } from '../pages/card/new';
import { BookmarksPage } from '../pages/bookmarks/bookmarks';

import { StatusBar } from '@ionic-native/status-bar';
import { SplashScreen } from '@ionic-native/splash-screen';
import { GoogleAnalytics } from '@ionic-native/google-analytics';


@Component({
  templateUrl: 'app.html'
})
export class MyApp {
  @ViewChild(Nav) nav: Nav;

  // set the root (or first) page
  rootPage = DiscoverPage;
  pages: Array<{title: string, component: any}>;
  profPage: any;

  constructor(
    public platform: Platform,
    public menu: MenuController,
    public statusBar: StatusBar,
    public splashScreen: SplashScreen,
    public ga: GoogleAnalytics,
  ) {
    this.initializeApp();

    // set our app's pages
    this.profPage = { title: 'Profile', component: ProfilePage };
    this.pages = [
      { title: 'Pitch My Idea', component: NewCardPage },
      // { title: 'Discover', component: DiscoverPage },
      // { title: 'Profile', component: ProfilePage },
      { title: "What I'm Following", component: BookmarksPage },
    ];
  }

  initializeApp() {
    this.platform.ready().then(() => {
      // Okay, so the platform is ready and our plugins are available.
      // Here you can do any higher level native things you might need.
      this.statusBar.styleDefault();
      this.splashScreen.hide();

      this.ga.startTrackerWithId('UA-86377634-1')
         .then(() => {
           console.log('Google analytics is ready now');
              this.ga.trackView('test');
           // Tracker is ready
           // You can now track pages or set additional information such as AppVersion or UserId
         })
         .catch(e => console.log('Error starting GoogleAnalytics', e));
    });
  }

  openPage(page) {
    // close the menu when clicking a link from the menu
    this.menu.close();
    // navigate to the new page if it is not the current page
    // this.nav.setRoot(page.component);
    this.nav.push(page.component);
  }
}
