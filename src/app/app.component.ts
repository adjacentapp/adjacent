import { Component, ViewChild } from '@angular/core';
import { Platform, MenuController, Nav, AlertController } from 'ionic-angular';

import { DiscoverPage } from '../pages/discover/discover';
import { ProfilePage } from '../pages/profile/profile';
import { NewCardPage } from '../pages/card/new';
// import { BookmarksPage } from '../pages/bookmarks/bookmarks';
import { ShowCardPage } from '../pages/card/show';
import { MessagesPage } from '../pages/messages/index';
import { FoundedPage } from '../pages/founded/founded';
import { MissionPage } from '../pages/mission/mission';


import { StatusBar } from '@ionic-native/status-bar';
import { SplashScreen } from '@ionic-native/splash-screen';
import { GoogleAnalytics } from '@ionic-native/google-analytics';

import { AuthProvider } from '../providers/auth/auth';
import { GlobalsProvider } from '../providers/globals/globals';

@Component({
  templateUrl: 'app.html'
})
export class MyApp {
  @ViewChild(Nav) nav: Nav;

  rootPage:any = 'LoginPage';
  profPage:{title: string, component: any, icon: string} = { title: 'Profile', component: ProfilePage, icon: 'md-person' };
  pages: Array<{title: string, component: any, icon: string}> = [
    { title: 'Pitch an Idea', component: NewCardPage, icon: 'md-create' },
    { title: 'My Ideas', component: FoundedPage, icon: 'md-bulb' },
    // { title: "Ideas I'm Following", component: BookmarksPage, icon: 'bookmark' },
    { title: "Messages", component: MessagesPage, icon: 'md-chatbubbles' },
  ];
  username = '';
  email = '';

  constructor(
    public platform: Platform,
    public menu: MenuController,
    public statusBar: StatusBar,
    public splashScreen: SplashScreen,
    // public ga: GoogleAnalytics,
    private auth: AuthProvider,
    private globsProv: GlobalsProvider,
    private alertCtrl: AlertController
  ){
    this.getGlobs();
    this.platform.ready().then( () => {
        this.statusBar.styleDefault();
        this.splashScreen.hide();
        this.initGA();
        this.checkDeepLink();
    });
  }

  getGlobs() {
    this.globsProv.getGlobs()
      .subscribe(
        data => console.log(data),
        error => console.log(<any>error)
      );
  }

  initGA() {
    // this.ga.startTrackerWithId('UA-86377634-1')
    //    .then(() => {
    //      console.log('Google analytics is ready now');
    //         this.ga.trackView('test');
    //      // Tracker is ready
    //      // You can now track pages or set additional information such as AppVersion or UserId
    //    })
    //    .catch(e => console.log('Error starting GoogleAnalytics', e));
  }

  checkDeepLink() {
    console.log('deep linking');
  }


  openPage(page) {
    this.menu.close();
    this.nav.push(page.component);
  }

  goToCard(item) {
    this.menu.close();
    this.nav.push(ShowCardPage, {
      item: item
    });
  }

  goToProfile(item) {
    this.menu.close();
    this.nav.push(ProfilePage, {
      user_id: item
    });
  }

  goToMission() {
    this.menu.close();
    this.nav.push(MissionPage);
  }

  logout() {
    let alert = this.alertCtrl.create({
      title: 'Logout?',
      message: 'Are you sure you want to logout?',
      buttons: [{
          text: 'Cancel',
          role: 'cancel',
          handler: () => console.log('Cancel clicked')
        },
        {
          text: 'Logout',
          handler: () => {
            this.auth.logout().subscribe(succ => {
              this.menu.close();
              this.nav.setRoot('LoginPage');
            });
          }
      }]
    });
    alert.present();
  }
}
