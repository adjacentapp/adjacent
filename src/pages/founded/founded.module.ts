import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { FoundedPage } from './founded';

@NgModule({
  declarations: [
    FoundedPage,
  ],
  imports: [
    IonicPageModule.forChild(FoundedPage),
  ],
  exports: [
    FoundedPage
  ]
})
export class FoundedPageModule {}
