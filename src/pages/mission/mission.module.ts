import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { MissionPage } from './mission';

@NgModule({
  declarations: [
    MissionPage,
  ],
  imports: [
    IonicPageModule.forChild(MissionPage),
  ],
  exports: [
    MissionPage
  ]
})
export class MissionPageModule {}
