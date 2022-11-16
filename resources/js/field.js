
import DetailField from './components/DetailField'
import FormField from './components/FormField'

Nova.booting((app, store) => {
  app.component("detail-text-editor", DetailField);
  app.component("form-text-editor", FormField);
})
