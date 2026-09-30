import { Composition } from "remotion";
import { BondiFilm, FILM_FRAMES, FPS } from "./Film";
import { Constellation, CONSTELLATION_FPS, CONSTELLATION_FRAMES, WINDOW_FPS, WINDOW_FRAMES } from "./Constellation";

export const Root = () => (
  <>
    <Composition id="BondiFilm" component={BondiFilm} durationInFrames={FILM_FRAMES} fps={FPS} width={1920} height={1080} />
    <Composition id="Constellation" component={Constellation} durationInFrames={CONSTELLATION_FRAMES} fps={CONSTELLATION_FPS}
                 width={1920} height={1080} />
    {/* The video over the website's app window: the film at 60 fps, from a full sky to a held end card. */}
    <Composition id="WindowFilm" component={Constellation} durationInFrames={WINDOW_FRAMES} fps={WINDOW_FPS}
                 width={1920} height={1080} defaultProps={{ endCard: true, fadeFromBlack: false, fadeToBlack: false }} />
  </>
);
