import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { PpPage } from './pp';

@NgModule({
  declarations: [
    PpPage,
  ],
  imports: [
    IonicPageModule.forChild(PpPage),
  ],
  exports: [
    PpPage
  ]
})
export class PpPageModule {}
