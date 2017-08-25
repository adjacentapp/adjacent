import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { TosPage } from './tos';

@NgModule({
  declarations: [
    TosPage,
  ],
  imports: [
    IonicPageModule.forChild(TosPage),
  ],
  exports: [
    TosPage
  ]
})
export class TosPageModule {}
