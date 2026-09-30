import { Composition } from "remotion";
import { BondiFilm, FILM_FRAMES, FPS } from "./Film";
import { Constellation, CONSTELLATION_FPS, CONSTELLATION_FRAMES, INTRO_FRAMES } from "./Constellation";

export const Root = () => (
  <>
    <Composition id="BondiFilm" component={BondiFilm} durationInFrames={FILM_FRAMES} fps={FPS} width={1920} height={1080} />
    <Composition id="Constellation" component={Constellation} durationInFrames={CONSTELLATION_FRAMES} fps={CONSTELLATION_FPS}
                 width={1920} height={1080} />
    {/* The window intro. Its background #26262a decodes to the app's #232427 after H.264 encoding. */}
    <Composition id="ConstellationIntro" component={Constellation} durationInFrames={INTRO_FRAMES} fps={CONSTELLATION_FPS}
                 width={2080} height={1000} defaultProps={{ endCard: false, background: "#26262a" }} />
  </>
);
